<?php

namespace HandlerCore\components;





use Closure;
use Exception;
use HandlerCore\Environment;
use HandlerCore\models\dao\AbstractBaseDAO;
use HandlerCore\models\dao\ConfigVarDAO;
use HandlerCore\models\dao\ReportDAO;
use HandlerCore\models\dao\ReportFilterDAO;
use HandlerCore\models\dao\SubReportDAO;
use HandlerCore\models\SimpleDAO;
use function HandlerCore\showMessage;

/**
 * Clase que genera tablas HTML utilizando la clase TableGenerator y configuraciones de la base de datos.
 */
class ReporterMaker  {
    private $report_id;
    private $sub_report_id;
    private bool $is_sub_report=false;
    private $matrix;
    private $root;
    private $definition;
    private ?array $defaults;
    private ?string $base_join = "";
    private $autoconfigurable;
    public $controls;
    public $html_attrs;


    /**
     * Define las cláusulas para el formato de las columnas en la tabla generada por el objeto TableGenerator.
     *
     * @var callable|null $col_clausure_definition Una función que define cómo se mostrarán las columnas.
     *                                            Recibe los datos de la fila, el nombre del campo y un indicador
     *                                            de si es el campo de los totales finales de la tabla.
     *                                            Ejemplo de uso:
     *                                            function($row, $field, $isTotal) {
     *                                                // ... (código para definir formato de la columna)
     *                                                return array("data" => $data, "style" => "border: 1px", "class" => "text-primary");
     *                                            };
     */
    private $col_clausure_definition;

    /**
     * Define las cláusulas para el formato de las filas en la tabla generada por el objeto TableGenerator.
     *
     * @var callable|null $row_clausure_definition Una función que define cómo se mostrarán las filas.
     *                                            Recibe los datos de la fila y retorna un arreglo con los atributos HTML
     *                                            que se generarán para la fila. Ejemplo de uso:
     *                                            function($row) {
     *                                                $result["style"] = "background: #efe970";
     *                                                return $result;
     *                                            };
     */
    private $row_clausure_definition;

    /**
     * Define las cláusulas para el formato de los totales en la tabla generada por el objeto TableGenerator.
     *
     * @var callable|null $totals_clausure_definition Una función que define cómo se mostrarán los totales.
     *                                               Recibe un arreglo acumulador de totales y los datos de la fila.
     *                                               Esta función se llama al procesar cada fila y retorna el acumulador
     *                                               actualizado. Ejemplo de uso:
     *                                               function($totals, $row) {
     *                                                   // ... (código para calcular y acumular los totales)
     *                                                   return $totals;
     *                                               };
     */
    private $totals_clausure_definition;

    private ?array $data_array = null;
    private $operators = array(
        "LIKE"=>"like",
        "NLIKE"=>"not like",
        "EQ"=>"=",
        "NEQ"=>"<>",
        "EMPTY"=>" is null",
        "NEMPTY"=>"is not null",
        "GT"=>">",
        "GET"=>">=",
        "LT"=>"<",
        "LET"=>"<=",
        "SQL"=>"",
        "BETN" => "BETWEEN",
        "IN" => "IN"

    );

    const FILTER_TAG = "{FILTERS_EXPLICIT}";

    const TYPE_GROUP = "GROUP";
    const TYPE_FILTER = "FILTER";

    /**
     * Genera un reporte a partir de la configuración del ID del reporte en la base de datos
     * @param $report_id
     * @param bool $subreport
     * @throws Exception
     */
    function __construct($report_id, bool $subreport = false) {

        $this->is_sub_report = $subreport;

        if($this->is_sub_report){
            $repDao = new  SubReportDAO();
            $repDao->getById(array("id"=>$this->report_id));


            $report_data = $repDao->get();


            $this->sub_report_id = $report_id;
            $this->report_id = $report_data["report_id"];
        }else{
            $this->report_id = $report_id;
        }


        $repDao = new  ReportDAO();
        $repDao->getById(array("id"=>$this->report_id));
        $repDao->escaoeHTML_OFF();
        $report_data = $repDao->get();
        $repDao->escaoeHTML_ON();

        if(empty($report_data)){
            throw new Exception("No existe el reporte con el ID: " . $report_id);
        }

        $this->definition = $report_data["definition"];

        $this->col_clausure_definition = $report_data["format_col"];
        $this->row_clausure_definition = $report_data["format_row"];
        $this->totals_clausure_definition = $report_data["format_totals"];

        $this->autoconfigurable = ($report_data["autoconfigurable"] == SimpleDAO::REG_ACTIVO_Y);


        $this->controls = null;

        if($this->autoconfigurable){
            if(!is_null($report_data["controls"]) && $report_data["controls"] != ""){
                $this->controls = explode(",",$report_data["controls"]);
            }


        }

        $this->html_attrs = $report_data["html_attrs"];

        #carga matriz definicion de filtros
        $this->loadMatrix();
    }

    private function getFilterDAO(ReportFilterDAO $filterDao, $noescape = true): ReportFilterDAO
    {

        if($noescape){
            $filterDao->escaoeHTML_OFF();
        }

        if($this->is_sub_report){
            $filterDao->getBySubReport($this->sub_report_id);
        }else{
            $filterDao->getWithBaseReportFilters($this->report_id);
        }

        if($noescape){
            $filterDao->escaoeHTML_ON();
        }

        return $filterDao;
    }

    /**
     * Carga y procesa la matriz de filtros para el informe, configurando
     * los valores predeterminados, el filtro raíz y las uniones base según sea necesario.
     *
     * @return void
     */
    private function  loadMatrix(): void
    {
        $this->defaults = array();
        $filterDao = new  ReportFilterDAO();

        $filterDao = $this->getFilterDAO($filterDao);
        $filterDao->escaoeHTML_OFF();
        $this->matrix = array();
        while ($filter = $filterDao->get()) {

            $this->matrix[$filter["id"]] = $filter;


            if($filter["root"] == SimpleDAO::REG_ACTIVO_Y || empty($this->root)){
                $this->root = $filter["id"];

                //si es el root y es vacio
                if( $filter["base_join"] != "EMPTY"){
                    $this->base_join =  $filter["base_join"];
                }


            }else if(empty($this->base_join ) && $filter["base_join"] != "EMPTY"){
                $this->base_join =  $filter["base_join"];
            }

            $obj_val = json_decode($filter["value"] ?? "",true);
            if($obj_val){
                if(isset($obj_val["default"])){
                    $filter["value"] = $obj_val["default"];
                }else{
                    $filter["value"] = "";
                }

            }

            //carga defaults si hay valor
            if(!empty($filter["value"])){
                $this->defaults["F_". $filter["id"]] = $filter["value"];
                $this->defaults["OP_". $filter["id"]] = $filter["op"];
                $this->defaults["J_". $filter["id"]] = $filter["join"];
            }



        }
        $filterDao->escaoeHTML_ON();

    }

    /**
     * Constructs the SQL query representing the filters for the report.
     *
     * @param string|null $from An identifier for the starting point in the matrix structure. This can represent a group or filter node.
     * @param null $data Optional. An associative array containing filter values where the keys are filter names, and values are the corresponding filter values.
     *
     * @return string The constructed SQL query string for the filters. If no filters are applicable, an empty string is returned.
     */
    private function builtFiltersSQL(?string $from, $data = null): string
    {
        $raw = "";

        #si es grupo imprime apertura
        if($this->matrix[$from]["type"] == self::TYPE_GROUP){
            $raw .= " ( ";
        }

        #si no es grupo
        if($this->matrix[$from]["type"] == self::TYPE_FILTER){

            //si el valor del filtro es vacio, no cargara el sql del filtro
            $data_filter_name = "F_" . $this->matrix[$from]["id"];

            if(isset($data[$data_filter_name]) && $data[$data_filter_name] != "''"){

                #imprime campo op valor
                $raw .= " " . $this->matrix[$from]["field"] . " {{OP_" . $this->matrix[$from]["id"] . "}} {F_" . $this->matrix[$from]["id"] . "} " ;



            }
        }else{
            #si es grupo

            #si hijo no es vacio
            if($this->matrix[$from]["child"]){
                #construye interno
                $raw .= $this->builtFiltersSQL($this->matrix[$from]["child"], $data);
            }

            #si es grupo imprime cierre

            $raw .= " ) ";


        }

        if(empty($this->matrix[$from]["sibling"])){
            // Obtener los keys del array
            $keys = array_keys($this->matrix);

            // Buscar el índice en el array de keys
            $posActual = array_search($from, $keys);

            // Verificar si hay un siguiente índice
            if ($posActual !== false && isset($keys[$posActual + 1])) {
                $this->matrix[$from]["sibling"] = $keys[$posActual + 1];
            }
        }

        #si siguiente no es vacio
        if($this->matrix[$from]["sibling"]){
            #construye siguiente
            $next_filters = $this->builtFiltersSQL($this->matrix[$from]["sibling"],$data);
            if(trim($raw) != "" && trim($next_filters) != ""){
                $raw .= " {J_" . $this->matrix[$from]["id"] . "} ";
            }

            $raw .= $next_filters;

        }


        return $raw;
    }

    private function builtFiltersHTML($from){
        $raw = "";

        #si es grupo imprime apertura
        if($this->matrix[$from]["type"] == self::TYPE_GROUP){

            $raw .= "<blockquote>";

        }

        #si no es grupo
        if($this->matrix[$from]["type"] == self::TYPE_FILTER){
            #imprime campo op valor


            $raw .= "<div class='form-group input-group' >";
            $raw .= "<span class='input-group-addon' >";
            $raw .= ($this->matrix[$from]["label"])? $this->matrix[$from]["label"] : $this->matrix[$from]["field"];
            $raw .= "</span>";

            $raw .= $this->htmlOperators($from);


            $raw .= "<input class='form-control' type='text' id='F_". $this->matrix[$from]["id"] ."' name='F_". $this->matrix[$from]["id"] ."' value='". $this->matrix[$from]["value"] ."' />";


            $raw = $this->htmlConjuntion($from) . $raw;

            $raw .= "</div>";


        }else{
            #si es grupo

            #si hijo no es vacio
            if($this->matrix[$from]["child"]){
                #construye interno
                $raw .= $this->builtFiltersHTML($this->matrix[$from]["child"]);
            }


            $raw .= "</blockquote>";

            #si existe otro hijo
            if($this->matrix[$from]["sibling"]){
                #imprime conjuncion

                $raw .= $this->htmlConjuntion($from);

            }
        }


        #si siguiente no es vacio
        if($this->matrix[$from]["sibling"]){
            #construye siguiente
            $raw .= $this->builtFiltersHTML($this->matrix[$from]["sibling"]);
        }

        return $raw;
    }

    private function htmlConjuntion($from){
        $raw = "";

        $raw .= "<span>";
        $raw .= "<select class='form-control' id='J_". $this->matrix[$from]["id"] ."' name='J_". $this->matrix[$from]["id"] ."' >";
        $conj = array("","AND","OR");
        foreach ($conj as $value) {
            $def = ($this->matrix[$from]["join"] == $value)? "selected" : "";
            $raw .= "<option value='$value' $def>$value</option>";
        }
        $raw .= "</select>";
        $raw .= "</span>";

        return $raw;
    }

    private function htmlOperators($from){
        $raw = "";

        $raw .= "<span>";
        $raw .= "<select class='form-control' id='OP_". $this->matrix[$from]["id"] ."' name='OP_". $this->matrix[$from]["id"] ."' >";
        $conj = array(
            "LIKE"=>showMessage("contiene"),
            "NLIKE"=>showMessage("no_contiene"),
            "EQ"=>showMessage("equals"),
            "NEQ"=>showMessage("no_equals"),
            "EMPTY"=>showMessage("empty"),
            "NEMPTY"=>showMessage("no_empty"),
            "GT"=>showMessage("gtetter_than"),
            "GET"=>showMessage("gtetter_equals_than"),
            "LT"=>showMessage("less_than"),
            "LET"=>showMessage("less_equals_than"),
            "SQL"=>showMessage("SQL"),
        );
        foreach ($conj as $key => $value) {
            $def = ($this->matrix[$from]["op"] == $key)? "selected" : "";
            $raw .= "<option value='$key' $def>$value</option>";
        }
        $raw .= "</select>";
        $raw .= "</span>";

        return $raw;
    }

    public function setDataArray(array $data_array): void
    {
        $this->data_array = $data_array;
    }

    private function getLoadedData(): array
    {
        return $this->data_array ?? $_POST;
    }

    public static function isValidJsonPath($path, $data): bool
    {
        // Divide la ruta en partes usando el punto como separador
        $partes = explode('.', $path);

        // Recorre las partes para verificar si existen en el array
        foreach ($partes as $parte) {
            if (isset($data[$parte])) {
                $data = $data[$parte];
            } else {
                return false; // Si alguna parte no existe, retorna falso
            }
        }
        return true; // Si todas las partes existen, retorna verdadero
    }

    public static function getJsonPathData($path, $source) {
        // Divide la ruta en partes usando el punto como separador
        $partes = explode('.', $path);

        // Recorre las partes para navegar en el array
        foreach ($partes as $parte) {
            if (isset($source[$parte])) {
                $source = $source[$parte]; // Avanza al siguiente nivel
            } else {
                return null; // Si alguna parte no existe, retorna null
            }
        }
        return $source; // Retorna el valor si todas las partes existen
    }



    /**
     * Generates and returns an array containing processed data based on default values, search criteria,
     * and filter configurations.
     *
     * The method processes default filters and applies incoming search criteria, ensuring that specified
     * filters are correctly set. It handles various filter types, including date, select fields, and custom
     * operations, while also managing filter conjunctions and SQL operations.
     *
     * @return array An associative array of processed data with filters, operations, and conjunctions
     *               prepared for further processing.
     */
    public function getDataArray(): array
    {
        $search_array= $this->getLoadedData();

        $data = array();
        //var_dump($this->defaults);
        //para cada filtro default
        foreach ($this->defaults as $key => $value) {
            //si viene en el post
            if(isset($search_array[$key]) && $search_array[$key] !== ""){
                //establece el valor del post
                $data[$key] = $search_array[$key];
            }else{
                //se queda el valor por default
                $data[$key] = $value;


            }
        }

        //var_dump($this->matrix);
        //para cada filtro
        foreach ($this->matrix as $filter_id => $filter_data) {
            $field = (!empty($filter_data["key_name"]))? $filter_data["key_name"] : $filter_data["label"];

            if(str_contains($field, ".") && self::isValidJsonPath($field, $search_array)){
                $search_array[$field] = self::getJsonPathData($field, $search_array);
            }

            if(!empty($search_array[$field])
                || !empty($search_array[$field . "_from"])
                || !empty($search_array[$field . "_to"])
                || isset($data["F_" . $filter_id])
                ) {


                switch ($filter_data["form_field_type"]) {
                    //si es una fecha
                    case FormMaker::FIELD_TYPE_DATE:
                    case FormMaker::FIELD_TYPE_DATETIME:
                        $from_time = "";
                        $to_time = "";
                        if ($filter_data["form_field_type"] == FormMaker::FIELD_TYPE_DATETIME) {
                            $from_time = " 00:00:00";
                            $to_time = " 23:59:59";
                        }

                        //si viene en el post el label del campo, con sufijo: _from
                        if (isset($search_array[$field . "_from"]) && $search_array[$field . "_from"] !== "") {

                            //es un campo de fecha y se almacena como un arreglo el from y el to
                            $data["F_" . $filter_id] = array(
                                $search_array[$field . "_from"] . $from_time,
                                $search_array[$field . "_to"] . $to_time
                            );
                        } else {
                            //intenta ver si el valor seteado es un json
                            $json = json_decode($data["F_" . $filter_id]);

                            //si el valor default es un objeto json
                            if ($json) {

                                $data["F_" . $filter_id] = array(
                                    date($json[0]),
                                    date($json[1])
                                );
                                //si por default no tenia un valor
                            } else {
                                $data["F_" . $filter_id] = array(
                                    date("Y-m-01" . $from_time),
                                    date("Y-m-d" . $to_time)
                                );
                            }

                        }
                        break;

                    case FormMaker::FIELD_TYPE_SELECT_ARRAY:
                    case FormMaker::FIELD_TYPE_SELECT:
                    case FormMaker::FIELD_TYPE_SELECT_I18N:

                        if(isset($data["F_" . $filter_id])){
                            //intenta ver si el valor seteado es un json
                            $json = json_decode($data["F_" . $filter_id], true);

                            //si el valor default es un objeto json
                            if ($json) {

                                if (isset($json["default"])) {
                                    $data["F_" . $filter_id] = $json["default"];
                                }


                            }
                        }



                        //si viene por post el valor, lo establece
                        if (isset($search_array[$field]) && $search_array[$field] != "") {
                            $data["F_" . $filter_id] = $search_array[$field];
                        }
                        break;

                    default:
                        //si no es un campo de fecha

                        //busca en el pos el label del campo
                        if (isset($search_array[$field]) && $search_array[$field] != "") {
                            $data["F_" . $filter_id] = $search_array[$field];
                            //si en el pos no vino el valor del filtro
                        }
                }

                $data["OP_" . $filter_id] = $filter_data["op"];
                switch ($data["OP_" . $filter_id]) {
                    case "LIKE":
                    case "NLIKE":
                        $data["F_" . $filter_id] = "'%" . SimpleDAO::escape($data["F_" . $filter_id]) . "%'";
                        break;

                    //los empty son establecidos a vacio
                    case "EMPTY":
                    case "NEMPTY":
                        $data["F_" . $filter_id] = "";
                        break;

                    //sql no escapa el valor
                    case "SQL":
                        $data["F_" . $filter_id] = $data["F_" . $filter_id];
                        break;

                    //sql no escapa el valor
                    case "BETN":
                        $data["F_" . $filter_id] = "'" . $data["F_" . $filter_id][0] . "' AND '" . $data["F_" . $filter_id][1] . "'";
                        //$data["F_" . $key] = $data["F_" . $key];
                        break;

                    //cualquier campo va entre comillas y escapado
                    default:
                        if(is_array($data["F_" . $filter_id])  ){


                            if(count($data["F_" . $filter_id]) === 1){
                                $data["F_" . $filter_id] = $data["F_" . $filter_id][0];
                            }else {
                                $data["OP_" . $filter_id] = "IN";
                            }

                        }

                        $data["F_" . $filter_id] = SimpleDAO::putQuoteAndNullSingleValue($data["F_" . $filter_id]);

                }

                //si la conjuncion de union es empty, la establece a vacio
                if ($filter_data["join"] == "EMPTY" || $filter_data["join"]=== "") {
                    $data["J_" . $filter_id] = "";
                }else{
                    $data["J_" . $filter_id] = "AND";
                }

            }
        }

        return $data;

    }

    /**
     * Obtiene la consulta SQL construida a partir de la definición del informe y los filtros aplicados.
     *
     * @return string La consulta SQL resultante que combina la definición del informe y los filtros aplicados.
     */
    public function getSQL(): string
    {


        $filtros = "";

        #si hay filtros que cargar

        if(count($this->matrix) > 0){
            $data = $this->getDataArray();

            #construye los filtros en sql
            $filtros = $this->builtFiltersSQL($this->root, $data);

            //echo $filtros . "<br />";

            #incrustra los parametros enviados por post
            $filtros = $this->embedParams($filtros, $data);

            //echo $filtros . "<br />";

            #incrustra los operadores al query
            $filtros = $this->embedParams($filtros, $this->operators);

            //echo $filtros . "<br />";

            if(trim($filtros) != ""){
                $filtros = " " . $this->base_join . $filtros;
            }
        }

        #incluye los filtros al query
        $sql = str_replace(self::FILTER_TAG, $filtros, $this->definition);

        #Incrusta otros filtros al query
        $sql = $this->embedParams($sql, $this->getLoadedData());

        return $sql;
    }

    /**
     * Obtiene el HTML que representa los filtros del informe.
     *
     * @return string|null El HTML generado que representa los filtros del informe o null si no hay filtros.
     */
    public function getHTML(): ?string
    {
        $filtros =null;



        if($this->root){
            $filtros = $this->builtFiltersHTML($this->root);
        }

        return $filtros;
    }

    /**
     * Incrusta los parámetros en un texto de etiqueta.
     *
     * @param string $tag       El texto de etiqueta que contiene las etiquetas a reemplazar.
     * @param array $data_array Un arreglo asociativo que contiene los datos para reemplazar las etiquetas.
     * @return string El texto de etiqueta con las etiquetas reemplazadas por los valores correspondientes.
     */
    public static function embedParams(string $tag, array $data_array): string
    {
        $conf = new ConfigVarDAO();
        $pattern = "/\{([\w.]+)\}/";

        preg_match_all($pattern, $tag, $matches, PREG_OFFSET_CAPTURE);

        for($i=0; $i < count($matches[0]); $i++){
            $foundKey = $matches[1][$i][0];

            if(!isset($data_array[$foundKey]) ) {

                $replaceWith = "{".$foundKey."}";
                if(self::isValidJsonPath($foundKey, $data_array)){
                    $conf_var = explode(".", $foundKey);

                    if ($conf_var[0] == Environment::$CONFIG_VAR_REPORT_TAG) {
                        $replaceWith = $conf->getVar($conf_var[1]);
                    }else if ($conf_var[0] == "system") {
                        $system = [
                            "username"  => Handler::getUsename(),
                            "user_id"   => Handler::getUserUID()
                        ];
                        $replaceWith = $system[$conf_var[1]] ?? "";
                    }else{
                        $replaceWith = self::getJsonPathData($foundKey, $data_array);
                    }
                }

            }else{
                $replaceWith = $data_array[$foundKey];
            }

            if(is_array($replaceWith)){
                $replaceWith = "(" . implode(",", $replaceWith) . ")";
            }

            $tag = str_replace("{".$foundKey."}", $replaceWith, $tag);
        }

        return $tag;
    }

    /**
     * Obtiene un objeto de acceso a datos (DAO) con el resultado del query de reporte.
     *
     * @param bool|null $autoconfigurable Opcional. Indica si el DAO debe ser autoconfigurable. Si se proporciona
     *                                     `true`, el DAO se marcará como autoconfigurable. Si se proporciona `false`,
     *                                     el DAO no será autoconfigurable. Si se omite, se usará el valor de
     *                                     autoconfigurable definido en la instancia del ReporterMaker.
     * @param bool $autoExec Opcional. Indica si se debe ejecutar automáticamente la consulta en el DAO.
     *                                     Si se establece como `true`, se ejecutará el query en el DAO. Si se
     *                                     establece como `false`, se creará el DAO sin ejecutar el query. El
     *                                     valor predeterminado es `true`.
     * @return AbstractBaseDAO Un objeto de acceso a datos (DAO) configurado con el resultado del query de reporte.
     * @throws Exception
     */
    public function getDAO($autoconfigurable = null, $autoExec = true): AbstractBaseDAO
    {

        $dao = new AbstractBaseDAO("",[],"","","");

        $sql = $this->getSQL();

        if($autoconfigurable || ($autoconfigurable===null && $this->autoconfigurable)){
            $dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
        }

        if(!$autoExec){
            $dao->disableExecFind();
        }
        $dao->find($sql);



        return $dao;
    }

    /**
     * Crea y devuelve un objeto de acceso a datos (DAO) con los resultados de un query SQL.
     *
     * @param string    $sql              La consulta SQL que se utilizará para obtener los resultados.
     * @param bool      $autoconfigurable Opcional. Indica si el DAO debe ser autoconfigurable. Si se proporciona
     *                                    `true`, el DAO se marcará como autoconfigurable. Si se proporciona `false`,
     *                                    el DAO no será autoconfigurable. El valor predeterminado es `false`.
     * @param bool      $autoExec         Opcional. Indica si se debe ejecutar automáticamente la consulta en el DAO.
     *                                    Si se establece como `true`, se ejecutará el query en el DAO. Si se
     *                                    establece como `false`, se creará el DAO sin ejecutar el query. El
     *                                    valor predeterminado es `true`.
     * @return AbstractBaseDAO Un objeto de acceso a datos (DAO) configurado con los resultados del query SQL.
     */
    public static function getDAOFromSQL($sql, $autoconfigurable = false, $autoExec= true){
        $dao = new AbstractBaseDAO("",[],"","","");



        if($autoconfigurable){
            $dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
        }

        if($autoExec){
            try{
                $dao->find($sql);
            }catch (Exception $e){

            }

        }



        return $dao;
    }

    /**
     * Devuelve un valor booleano que indica si el objeto actual es un subreporte.
     *
     * @return bool `true` si el objeto actual es un subreporte, `false` si no lo es.
     */
    public function getIsSubReport(): bool
    {
        return $this->is_sub_report;
    }

    /**
     * Devuelve el identificador (ID) asociado al objeto actual.
     *
     * @return string El ID del objeto actual, que puede ser el ID del reporte o el subreporte, según corresponda.
     */
    public function getID(): string{
        return ($this->is_sub_report)? $this->sub_report_id : $this->report_id;
    }

    /**
     * Genera y devuelve un formulario de filtro utilizando la clase FormMaker.
     *
     * @param FormMaker|null $form El formulario a utilizar, si no se proporciona se creará uno nuevo.
     * @param null $start_values Valores iniciales para los campos del formulario.
     * @return FormMaker El formulario de filtro generado.
     */
    public function getFormFilter(FormMaker $form = null, $start_values=null): FormMaker
    {
        if(!$form){
            $form = new FormMaker();
        }
        $filterDao = new  ReportFilterDAO();

        $filterDao = $this->getFilterDAO($filterDao);
        $filterDao->escaoeHTML_OFF();
        while ($filter = $filterDao->get()) {
            $field = $filter["label"];

            if($start_values && isset($start_values[$field])){
                $filter["value"] = $start_values[$field];
            }

            switch ($filter["form_field_type"]) {

                //si es una fecha
                case FormMaker::FIELD_TYPE_DATE:
                case FormMaker::FIELD_TYPE_DATETIME:
                    $field_name_from = $field . "_from";
                    $field_name_to = $field . "_to";


                    $default_from = null;
                    $default_to = null;



                    if($start_values && isset($start_values[$field_name_from])){
                        $default_from = $start_values[$field_name_from];
                    }

                    if($start_values && isset($start_values[$field_name_to])){
                        $default_to = $start_values[$field_name_to];
                    }

                    //si el valor por defecto no es nulo
                    if($filter["value"] != ''){
                        //intenta convertirlo a objeto
                        $json_obj = json_decode($filter["value"]);

                        //si pudo converitlo
                        if($json_obj){
                            if(!$default_from && isset($json_obj[0])){
                                $default_from = date($json_obj[0]);
                            }

                            if(!$default_to && isset($json_obj[1])){
                                $default_to = date($json_obj[1]);
                            }
                        }else{
                            $default_from = $filter["value"];
                            $default_to = $filter["value"];
                        }
                    }

                    //establece campo from
                    $field = $field_name_from;

                    $form->prototype[$field] = $default_from;

                    $form->defineField(array(
                        "campo"=>$field,
                        "tipo" =>FormMaker::FIELD_TYPE_DATE,
                    ));

                    //establece campo to
                    $field = $field_name_to;

                    $form->prototype[$field] = $default_to;

                    $form->defineField(array(
                        "campo"=>$field,
                        "tipo" =>FormMaker::FIELD_TYPE_DATE,
                    ));
                    break;

                case  FormMaker::FIELD_TYPE_SELECT_ARRAY:
                    $default = "";
                    $source = array();

                    //si el valor por defecto no es nulo
                    if($filter["value"] != ''){
                        //intenta convertirlo a objeto
                        $json_obj = json_decode($filter["value"],true);

                        //si pudo convertirlo
                        if($json_obj){
                            if(isset($json_obj["default"])){
                                $default = $json_obj["default"];
                            }

                            if(isset($json_obj["source"])){
                                $source = $json_obj["source"];
                            }
                        }
                    }

                    $form->prototype[$field] = $default;

                    $form->defineField(array(
                        "campo"=>$field,
                        "tipo" =>$filter["form_field_type"],
                        "source"=>$source
                    ));
                    break;

                case  FormMaker::FIELD_TYPE_SELECT:
                    $default = "";
                    $source = null;

                    $dao_name = null;
                    $dao_method = null;
                    $dao_method_params = null;
                    $dao_select_id = null;
                    $dao_select_name = null;
                    $fiel_type = FormMaker::FIELD_TYPE_TEXT;

                    //si el valor por defecto no es nulo
                    if($filter["value"] != ''){

                        //intenta convertirlo a objeto
                        $json_obj = json_decode($filter["value"],true);

                        //si pudo convertirlo
                        if($json_obj){


                            //establece defalt
                            if(isset($json_obj["default"])){
                                $default = $json_obj["default"];
                            }

                            //busca el dao
                            if(isset($json_obj["dao"])){
                                $dao_name = $json_obj["dao"];
                            }

                            if(isset($json_obj["method"])){
                                $dao_method = $json_obj["method"];
                            }

                            if(isset($json_obj["methodParams"])){
                                $dao_method_params = $json_obj["methodParams"];
                            }

                            if(isset($json_obj["selectID"])){
                                $dao_select_id = $json_obj["selectID"];
                            }

                            if(isset($json_obj["selectName"])){
                                $dao_select_name = $json_obj["selectName"];
                            }

                            //valida que exista el dao
                            if(!class_exists($dao_name)){
                                $dao_name = Environment::$NAMESPACE_MODELS .  $dao_name;
                            }

                            //valida nuevamente que este cargado el dao
                            if(class_exists($dao_name)){
                                //crea un objeto del tipo del dao
                                $source = new $dao_name();

                                //valida que exista el metodo
                                if(method_exists($source, $dao_method)){
                                    $source->$dao_method();
                                    $source->selectID = $dao_select_id;
                                    $source->selectName = $dao_select_name;


                                    $fiel_type = $filter["form_field_type"];

                                }
                            }
                        }
                    }

                    $form->defineField(array(
                        "campo"=>$field,
                        "tipo" =>$fiel_type,
                        "source"=>$source
                    ));

                    $form->prototype[$field] = $default;
                    break;


                case  FormMaker::FIELD_TYPE_SELECT_I18N:

                    //si el valor por defecto no es nulo
                    if($filter["value"] != ''){
                        $default = "";
                        $source = array();

                        //intenta convertirlo a objeto
                        $json_obj = json_decode($filter["value"],true);

                        //si pudo convertirlo
                        if($json_obj){
                            if(isset($json_obj["default"])){
                                $default = $json_obj["default"];
                            }

                            if(isset($json_obj["source"])){
                                $source = $json_obj["source"];
                            }
                        }
                    }

                    $form->prototype[$field] = $default;

                    $form->defineField(array(
                        "campo"=>$field,
                        "tipo" =>$filter["form_field_type"],
                        "source"=>$source
                    ));
                    break;

                default:
                    //si no es una fecha
                    $form->prototype[$field] = $filter["value"];

                    $form->defineField(array(
                        "campo"=>$field,
                        "tipo" =>$filter["form_field_type"],
                    ));
            }


        }
        $filterDao->escaoeHTML_ON();

        return $form;
    }

    /**
     * Devuelve la función de clausura para formatear una columna en la tabla generada.
     *
     * @return Closure|null La función de clausura o nulo si no está definida.
     */
    public function getColClausure(): ?Closure
    {
        $clausure = null;

        if($this->col_clausure_definition && $this->col_clausure_definition != ""){
            $clausure = function($row, $field, $isTotal){
                $htmlCol = null;
                eval($this->col_clausure_definition);
                return $htmlCol;
            };
        }

        return $clausure;
    }

    /**
     * Devuelve la función de clausura para formatear una fila en la tabla generada.
     *
     * @return Closure|null La función de clausura o nulo si no está definida.
     */
    public function getRowClausure(): ?Closure
    {
        $clausure = null;

        if($this->row_clausure_definition && $this->row_clausure_definition != ""){
            $clausure = function($row){
                $result=null;
                eval($this->row_clausure_definition);
                return $result;
            };
        }

        return $clausure;
    }

    /**
     * Devuelve la función de clausura para calcular totales en la tabla generada.
     *
     * @return Closure|null La función de clausura o nulo si no está definida.
     */
    public function getTotalsClausure(): ?Closure
    {
        $clausure = null;

        if($this->totals_clausure_definition && $this->totals_clausure_definition != ""){
            $clausure = function($totals, $row){
                eval($this->totals_clausure_definition);
                return $totals;
            };
        }

        return $clausure;
    }
}

