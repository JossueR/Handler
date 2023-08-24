<?php

namespace HandlerCore\components;





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
 *
 */
class ReporterMaker  {
    private $report_id;
    private $subreport_id;
    private $is_subreport;
    private $matrix;
    private $root;
    private $definition;
    private $defaults;
    private $base_join;
    private $autoconfigurable;
    public $controls;
    public $html_attrs;


    private $col_clausure_definition;
    private $row_clausure_definition;
    private $totals_clausure_definition;
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
        "BETN" => "BETWEEN"

    );

    const FILTER_TAG = "{FILTERS_EXPLICIT}";

    const TYPE_GROUP = "GROUP";
    const TYPE_FILTER = "FILTER";

    function __construct($report_id, $subreport = false) {

        $this->is_subreport = $subreport;

        if($this->is_subreport){
            $repDao = new  SubReportDAO();
            $repDao->getById(array("id"=>$this->report_id));


            $report_data = $repDao->get();


            $this->subreport_id = $report_id;
            $this->report_id = $report_data["report_id"];
        }else{
            $this->report_id = $report_id;
        }

        $this->base_join = "";

        $repDao = new  ReportDAO();
        $repDao->getById(array("id"=>$this->report_id));
        $repDao->escaoeHTML_OFF();
        $report_data = $repDao->get();
        $repDao->escaoeHTML_ON();
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
    }

    private function getFilterDAO(ReportFilterDAO $filterDao, $noescape = true){

        if($noescape){
            $filterDao->escaoeHTML_OFF();
        }

        if($this->is_subreport){
            $filterDao->getBySubReport($this->subreport_id);
        }else{
            $filterDao->getByReport($this->report_id);
        }

        if($noescape){
            $filterDao->escaoeHTML_ON();
        }

        return $filterDao;
    }

    private function  loadMatrix(){
        $this->defaults = array();
        $filterDao = new  ReportFilterDAO();

        $filterDao = $this->getFilterDAO($filterDao);
        $filterDao->escaoeHTML_OFF();
        $this->matrix = array();
        while ($filter = $filterDao->get()) {
            $this->matrix[$filter["id"]] = $filter;

            if($filter["root"] == SimpleDAO::REG_ACTIVO_Y){
                $this->root = $filter["id"];

                //si es el root y es vacio
                if( $filter["base_join"] != "EMPTY"){
                    $this->base_join =  $filter["base_join"];
                }


            }

            $obj_val = json_decode($filter["value"],true);
            if($obj_val){
                if(isset($obj_val["default"])){
                    $filter["value"] = $obj_val["default"];
                }else{
                    $filter["value"] = "";
                }

            }

            $this->defaults["F_". $filter["id"]] = $filter["value"];
            $this->defaults["OP_". $filter["id"]] = $filter["op"];
            $this->defaults["J_". $filter["id"]] = $filter["join"];

        }
        $filterDao->escaoeHTML_ON();

    }

    private function builtFiltersSQL($from, $data = null){
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

                #imprime conjuncion
                //$raw = " {J_" . $this->matrix[$from]["id"] . "} " . $raw ;

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

            #si existe otro hijo
            if($this->matrix[$from]["sibling"]){
                #imprime conjuncion
                //$raw .= " {J_" . $this->matrix[$from]["id"] . "} ";

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

    /***
     * Obtiene un arreglo con los datos default y los que el usuario sobre escribio y vienen por post
     */
    private function getDataArray(){
        $search_array=$_POST;

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

        //para cada filtro
        foreach ($this->matrix as $key => $filter_data) {
            $field = $filter_data["label"];


            switch($filter_data["form_field_type"]){
                //si es una fecha
                case FormMaker::FIELD_TYPE_DATE:
                case FormMaker::FIELD_TYPE_DATETIME:
                    $from_time = "";
                    $to_time = "";
                    if($filter_data["form_field_type"] == FormMaker::FIELD_TYPE_DATETIME){
                        $from_time = " 00:00:00";
                        $to_time = " 23:59:59";
                    }

                    //si viene en el post el label del campo, con sufijo: _from
                    if(isset($search_array[$field . "_from"]) && $search_array[$field . "_from"] !== ""){

                        //es un campo de fecha y se almacena como un arreglo el from y el to
                        $data["F_" . $key] = array(
                            $search_array[$field . "_from"] . $from_time,
                            $search_array[$field . "_to"] . $to_time
                        );
                    }else{
                        //intenta ver si el valor seteado es un json
                        $json = json_decode($data["F_" . $key]);

                        //si el valor default es un objeto json
                        if($json){

                            $data["F_" . $key] = array(
                                date($json[0]),
                                date($json[1])
                            );
                            //si por default no tenia un valor
                        }else{
                            $data["F_" . $key] = array(
                                date("Y-m-01" . $from_time),
                                date("Y-m-d" . $to_time)
                            );
                        }

                    }
                    break;

                case FormMaker::FIELD_TYPE_SELECT_ARRAY:
                case FormMaker::FIELD_TYPE_SELECT:
                case FormMaker::FIELD_TYPE_SELECT_I18N:

                    //intenta ver si el valor seteado es un json
                    $json = json_decode($data["F_" . $key], true);

                    //si el valor default es un objeto json
                    if($json){

                        if(isset($json["default"]) ){
                            $data["F_" . $key] = $json["default"];
                        }


                    }

                    //si viene por post el valor, lo establece
                    if(isset($search_array[$field]) && $search_array[$field] != ""){
                        $data["F_" . $key] = $search_array[$field];
                    }
                    break;

                default:
                    //si no es un campo de fecha

                    //busca en el pos el label del campo
                    if(isset($search_array[$field]) && $search_array[$field] != ""){
                        $data["F_" . $key] = $search_array[$field];
                        //si en el pos no vino el valor del filtro
                    }
            }

            switch($data["OP_" . $key]){
                case "LIKE":
                case "NLIKE":
                    $data["F_" . $key] = "'%" . SimpleDAO::escape($data["F_" . $key]) . "%'";
                    break;

                //los empty son establecidos a vacio
                case "EMPTY":
                case "NEMPTY":
                    $data["F_" . $key] = "";
                    break;

                //sql no escapa el valor
                case "SQL":
                    $data["F_" . $key] = $data["F_" . $key];
                    break;

                //sql no escapa el valor
                case "BETN":
                    $data["F_" . $key] = "'" . $data["F_" . $key][0] . "' AND '". $data["F_" . $key][1]. "'";
                    //$data["F_" . $key] = $data["F_" . $key];
                    break;

                //cualquier campo va entre comillas y escapado
                default:
                    $data["F_" . $key] = "'" . SimpleDAO::escape($data["F_" . $key]) . "'";
            }

            //si la conjuncion de union es empty, la establece a vacio
            if($data["J_" . $key] == "EMPTY"){
                $data["J_" . $key] = "";
            }


        }

        return $data;

    }

    public function getSQL(){
        #carga matriz definicion de filtros
        $this->loadMatrix();

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

        #TODO Incrusta otros filtros al query

        $sql = $this->embedParams($sql, $_POST);

        return $sql;
    }

    public function getHTML(){
        $filtros =null;
        $this->loadMatrix();


        if($this->root){
            $filtros = $this->builtFiltersHTML($this->root);
        }

        return $filtros;
    }

    public static function embedParams($tag, $data_array){
        $conf = new ConfigVarDAO();
        $pattern = "/\{([\w.]+)\}/";

        preg_match_all($pattern, $tag, $matches, PREG_OFFSET_CAPTURE);

        for($i=0; $i < count($matches[0]); $i++){
            $foundKey = $matches[1][$i][0];

            if(!isset($data_array[$foundKey]) || $data_array[$foundKey] === null) {

                $conf_var = explode(".", $foundKey);

                if ($conf_var[0] == Environment::$CONFIG_VAR_REPORT_TAG) {
                    $replaceWith = $conf->getVar($conf_var[1]);
                }else if(isset($data_array[$conf_var[0]]) && is_array($data_array[$conf_var[0]])) {

                    //return self::embedParams($tag,Handler::getRequestAttr($conf_var[0]));
                    $replaceWith = $data_array[$conf_var[0]][$conf_var[1]];
                }else{
                    $replaceWith = "{".$foundKey."}";
                }


            }else{
                $replaceWith = $data_array[$foundKey];
            }

            $tag = str_replace("{".$foundKey."}", $replaceWith, $tag);
        }

        return $tag;
    }

    public function getDAO($autoconfigurable = null, $autoExec = true){

        $dao = new AbstractBaseDAO("","","","","");

        $sql = $this->getSQL();

        if($autoconfigurable || ($autoconfigurable===null && $this->autoconfigurable)){
            $dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
        }

        if($autoExec){

            $dao->find($sql);
        }



        return $dao;
    }

    /**
     * @return AbstractBaseDAO
     */
    public static function getDAOFromSQL($sql, $autoconfigurable = false, $autoExec= true){
        $dao = new AbstractBaseDAO("","","","","");



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

    public function getIsSubReport(){
        return $this->is_subreport;
    }

    public function getID(){
        return ($this->is_subreport)? $this->subreport_id : $this->report_id;
    }

    public function getFormFilter(FormMaker $form = null, $start_values=null){
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

    public function getColClausure(){
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

    public function getRowClausure(): ?\Closure
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

    public function getTotalsClausure(): ?\Closure
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

