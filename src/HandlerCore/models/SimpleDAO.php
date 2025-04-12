<?php
/**
 *Create Date: 07/22/2011 01:00:56
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 124 $
 */

namespace HandlerCore\models;

use Exception;
use HandlerCore\components\Handler;
use HandlerCore\Environment;
use HandlerCore\models\dao\QueryParams;
use function HandlerCore\validDate;

/**
 * Clase base que proporciona métodos para acceder y manipular la base de datos.
 * Sirve como punto de acceso y abstracción para consultas y manipulación de datos.
 * Puede ser utilizada de manera estática o instanciada y es la base para construir DAOs.
 */
class SimpleDAO{
    static private $_vars;
    static private bool $conectado=false;
    const REG_ACTIVO='1';
    const REG_DESACTIVADO='0';
    const REG_ACTIVO_TX='ACTIVE';
    const REG_DESACTIVADO_TX='INACTIVE';
    const REG_ACTIVO_Y='Y';
    const REG_DESACTIVADO_N='N';
    const EDIT=1;
    const INSERT=0;

    const NO_REMOVE_TAG=FALSE;
    const REMOVE_TAG=TRUE;

    const AND_JOIN = " AND ";
    const OR_JOIN = " OR ";

    const IS_SELECT = TRUE;
    const IS_AUTOCONFIGURABLE = TRUE;

    protected $tableName;

    //arreglo con los nombres de los campos que son el id de la tabla
    protected $TablaId;

    public static $SQL_TAG ="<SQL>";

    public static $inAssoc =true;

    public static $inArray = true;

    /**
     * @var Connection $conections
     */
    private static $conections = array();

    public static $defaultConection = null;

    public static $enableRecordLog = false;

    public static $recordTable = "record";

    public static $debugTable = "debug_log";

    public static $enableDebugLog = false;

    protected static $escapeHTML = true;

    private static $debugTAG;



    /**
     * Constructor de la clase SimpleDAO.
     *
     * @param string $tableName El nombre de la tabla en la base de datos.
     * @param array $id El identificador único asociado a la tabla.
     */
    function __construct(string $tableName, array $id){
        $this->tableName=$tableName;

        $this->TablaId=$id;
    }

    public static function isConnected(): bool
    {
        return self::$conectado;
    }

    /**
     * Processes a given value by adding quotes, handling null values, and optionally removing SQL tags.
     *
     * @param mixed $value The value to be processed. Can be of any type.
     * @param array $array An array parameter, though not explicitly used in this method.
     * @param int|string $key The key associated with the value, though not explicitly used in this method.
     * @param bool $removeTag Indicates whether to remove the SQL tag from the value if present.
     * @return array|string Returns the processed value:
     *                       - If the value is null or empty, returns "null".
     *                       - If the value contains the SQL tag, it either removes the tag or retains it based on the $removeTag parameter.
     *                       - If the value does not contain the SQL tag, returns the value enclosed in single quotes after escaping.
     *                       - If the value is an array, delegates processing to the putQuoteAndNull method.
     */
    public static function putQuoteAndNullSingleValue(mixed $value, bool $removeTag=false): array|string
    {
        //si el valor no es un array
        if (!is_array($value)) {

            //Pone valor null si el elemento es nulo o vacio
            if (is_null($value) || strlen($value) == 0) {
                return "null";
            } else {

                //Si el elemento contiene el tag <SQL>
                if (substr_count($value, self::$SQL_TAG) > 0) {

                    //Elimina el tag si se configura
                    if ($removeTag) {
                        //Elimina el tag <SQL>
                        $value = str_replace(self::$SQL_TAG, "", $value);
                    }


                    //no realiza ninguna conversion
                    return $value;

                    //Elemente no tiene tag <SQL>
                } else {

                    //Agrega comillas
                    return "'" . self::escape($value) . "'";
                }


            }
        } else {
            return self::putQuoteAndNull($value);
        }
    }

    /**
     * Obtiene el nombre de la tabla en la base de datos asociada a este DAO.
     *
     * @return string El nombre de la tabla en la base de datos.
     */
    function getTableName(){
        return $this->tableName;
    }

    /**
     * Obtiene el identificador único asociado a la tabla en la base de datos.
     *
     * @return int El identificador único de la tabla.
     */
    function getId(){
        return $this->TablaId;
    }

    /**
     * Establece una conexión con la base de datos usando los parámetros proporcionados.
     *
     * @param string $host El nombre del host del servidor de la base de datos.
     * @param string $bd El nombre de la base de datos a la que se desea conectar.
     * @param string $usuario El nombre de usuario para autenticación en la base de datos.
     * @param string $pass La contraseña del usuario para autenticación en la base de datos.
     * @param string|null $conectionName (Opcional) El nombre de la conexión. Se utiliza para identificar la conexión en el conjunto de conexiones.
     * @return bool Retorna verdadero si la conexión se establece correctamente, de lo contrario, falso.
     */
    static function connect(string $host, string $bd, string $usuario, string $pass, ?string $conectionName='db'): bool
    {
        self::$conectado = false;


        $coneccion=mysqli_connect($host,$usuario,$pass);
        if($coneccion){
            $bdConexion=mysqli_select_db($coneccion, $bd);

            if($bdConexion){
                @mysqli_query($coneccion, "SET NAMES 'utf8'");
                @mysqli_query($coneccion, "SET time_zone = '-5:00'");
                self::$conectado = true;

                $conectionName = (!$conectionName)? count(self::$conections)+1 : $conectionName;

                self::$conections[$conectionName] = new Connection($host,$bd,$usuario,$pass, $coneccion);

                //la coneccion por defecto es la ultima
                self::$defaultConection = $conectionName;
            }
        }



        return self::$conectado;
    }

    /**
     * Escapa una cadena de texto para ser segura para su uso en consultas SQL.
     *
     * @param string $str La cadena de texto que se desea escapar.
     * @return string La cadena de texto escapada y segura para ser utilizada en consultas SQL.
     */
    static public function escape(string $str): string
    {
        return mysqli_real_escape_string(self::getConnectionData()->connection, $str);
    }





    /**
     * Ejecuta una consulta SQL en la base de datos.
     *
     * @param string $sql La consulta SQL a ejecutar.
     * @param bool $isSelect Indica si la consulta es de tipo SELECT (true) o no (false).
     * @param bool $isAutoConfigurable Indica si se deben aplicar automáticamente filtros, ordenamiento y paginación.
     * @param string|null $connectionName El nombre de la conexión a utilizar. Si es nulo, se utiliza la conexión por defecto.
     * @return QueryInfo Un objeto que contiene información sobre el resultado de la consulta.
     * @throws Exception Si ocurre un error en la conexión o en la ejecución de la consulta.
     */
    static public function &execQuery(string $sql, ?bool $isSelect= true, ?bool $isAutoConfigurable= false, ?string $connectionName=null, ?QueryParams $qSettings=null): QueryInfo
    {
        $summary = new QueryInfo();



        // Si no se proporciona un nombre de conexión válido, se utiliza la conexión por defecto
        if(!$connectionName || !isset(self::$conections[$connectionName])){
            $connectionName = self::$defaultConection;
        }

        // Si es necesario, se aplican automáticamente filtros, ordenamiento y paginación
        if($isAutoConfigurable){
            $qSettings = QueryParams::buildRequestQueryParams($qSettings);

            $sql = self::addGroups($sql);
            $sql = self::addFilters($sql, $qSettings);
            $sql = self::addOrder($sql, $qSettings);

            #excel no pagina
            if(Handler::getRequestAttr(Handler::OUTPUT_FORMAT) != Handler::FORMAT_EXCEL){
                $sql = self::addPagination($sql, $qSettings);
            }

        }

        /// Muestra el SQL si está habilitado el modo depuración
        if(self::getDataVar("SQL_SHOW")){
            echo $sql . "<br />\n";
        }


        // Ejecuta la consulta SQL y almacena el resultado en el objeto QueryInfo
        $summary->result = @mysqli_query(self::$conections[$connectionName]->connection, $sql );


        if($isSelect){

            $summary->total  = ($summary->result)? intval(mysqli_num_rows($summary->result)) : 0;
        }else{
            $summary->total = mysqli_affected_rows(self::$conections[$connectionName]->connection);
            $summary->new_id = mysqli_insert_id(self::$conections[$connectionName]->connection);
        }


        $summary->errorNo = mysqli_errno(self::$conections[$connectionName]->connection);

        $summary->error = mysqli_error(self::$conections[$connectionName]->connection);

        // Almacena el log si está habilitado
        self::storeDebugLog(self::$conections[$connectionName]->connection, $sql);

        // Almacena el último SQL ejecutado en el objeto QueryInfo
        $summary->sql = $sql;

        // Si hay paginación automática, se obtiene el total de filas
        if($isAutoConfigurable){
            if($qSettings->getPaginationMode() == PaginationMode::SQL_CALC_FOUND_ROWS){
                $sql = "SELECT FOUND_ROWS();";
                $rows = @mysqli_query( self::$conections[$connectionName]->connection, $sql);
                $rows = mysqli_fetch_row($rows);
                $summary->allRows = $rows[0];
            }else{

                $summary->allRows =  (($qSettings->getPage()+1) * $qSettings->getCantByPage()) + 1;
            }




        }else{
            $summary->allRows = $summary->total;
        }

        // Muestra el SQL si está habilitado el modo depuración
        if(self::getDataVar("SQL_SHOW")){
            echo $summary->error;
        }



        return $summary;
    }

    /**
     * Ejecuta una consulta SQL que no devuelve resultados en la base de datos.
     *
     * @param string $sql La consulta SQL a ejecutar.
     * @param string|null $connectionName El nombre de la conexión a utilizar. Si es nulo, se utiliza la conexión por defecto.
     * @return bool Indica si la consulta se ejecutó correctamente (true) o si ocurrió un error (false).
     * @throws Exception Si ocurre un error en la conexión o en la ejecución de la consulta.
     */
    static public function execNoQuery($sql, $connectionName=null): bool
    {
        $summary = SimpleDAO::execQuery($sql,false,FALSE,$connectionName);

        return ($summary->errorNo == 0);
    }


    /**
     * Construye una consulta SQL reemplazando marcadores especiales con valores proporcionados en un arreglo.
     *
     * Utiliza expresiones regulares para encontrar los marcadores especiales /*{...} * / en la consulta y los reemplaza según la lógica definida.
     * Los marcadores especiales como /*{create_date} * / se reemplazan con la fecha y hora actual,
     * mientras que los marcadores como /*{create_user} * / se reemplazan con el ID del usuario actual almacenado en la sesión. Los demás marcadores se reemplazan con los valores correspondientes del arreglo.
     * @param string $sql La consulta SQL con marcadores especiales a reemplazar.
     * @param array $array Un arreglo asociativo que contiene los valores para reemplazar los marcadores.
     * @return string La consulta SQL resultante con los marcadores reemplazados por los valores del arreglo.
     */
    static private function builtQuery($sql, $array): string
    {
        $pattern = "#/\*\{(.*)\}\*/#";
        preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE);


        for($i=0; $i < count($matches[0]); $i++){
            switch ($matches[0][$i][0]) {
                case "/*{create_date}*/":
                case "/*{modify_date}*/":
                    $replaceWith = "NOW()";
                    break;

                case "/*{create_user}*/":
                case "/*{modify_user}*/":
                    $replaceWith = $_SESSION['USER_ID'];
                    break;

                default:
                    if(!isset($array[$matches[1][$i][0]]) || $array[$matches[1][$i][0]] == null){
                        $replaceWith = "null";
                    }else{
                        $replaceWith =$array[$matches[1][$i][0]];
                    }
                    break;
            }
            $sql = str_replace($matches[0][$i][0], $replaceWith, $sql);
        }
        return $sql;
    }


    /**
     * Ejecuta una consulta SQL y recupera la primera fila de resultados como un arreglo o un valor único.
     *
     * @param string $sql La consulta SQL a ejecutar.
     * @param string|null $conectionName El nombre de la conexión a utilizar (opcional).
     * @param bool|array|null $inArray Define si los resultados deben ser devueltos en un arreglo o si se busca un campo específico (opcional).
     * @return mixed|null Devuelve la primera fila de resultados como un arreglo o un valor único dependiendo del parámetro $inArray. Devuelve null si ocurre un error.
     * @throws Exception
     */
    static public function execAndFetch($sql, $conectionName= null, $inArray=null){
        $summary = self::execQuery($sql, true,false,$conectionName);

        if($inArray !== null){
            $summary->inArray=$inArray;
        }

        $row = self::getNext($summary);

        $resp = null;

        if($summary->errorNo == 0){
            //si solo se estaba buscando un campo
            if($row && self::getNumFields($summary) == 1){
                //obtener el primer campo
                $resp = reset($row);
            }else{
                $resp =  $row;
            }
        }


        return $resp;
    }

    /**
     * Obtiene la siguiente fila de resultados de una consulta y la devuelve como un arreglo.
     *
     * @param QueryInfo $summary El objeto QueryInfo que contiene información sobre la consulta.
     * @return array|null Devuelve la siguiente fila de resultados como un arreglo asociativo o numérico, o null si no hay más filas.
     */
    static public function getNext(QueryInfo &$summary): ?array
    {

        if(!isset($summary->total) || $summary->total == 0){
            return null;
        }else if(self::$inArray){

            if(self::$inAssoc){
                $type=MYSQLI_ASSOC;
            }else{
                $type= MYSQLI_NUM;
            }

            return self::escape_HTML(mysqli_fetch_array($summary->result, $type));
        }else{
            return self::escape_HTML(mysqli_fetch_row($summary->result));
        }
    }

    /**
     * Obtiene todas las filas de resultados de una consulta y las devuelve en un arreglo multidimensional.
     *
     * @param QueryInfo $sumary El objeto QueryInfo que contiene información sobre la consulta.
     * @return array Un arreglo multidimensional que contiene todas las filas de resultados.
     */
    static public function getAll(QueryInfo &$sumary){
        $valores = array();

        while($row = self::getNext($sumary)){
            $valores[] = $row;
        }

        return $valores;
    }

    /**
     * Limpia un arreglo eliminando elementos con valores vacíos.
     *
     * @param array $searchArray El arreglo a ser limpiado.
     * @return array Un nuevo arreglo con los elementos no vacíos del arreglo original.
     */
    static public function cleanEmptys($searchArray): array
    {
        $cleanArray = array();

        foreach ($searchArray as $key => $value) {
            if(! empty($value) ){
                $cleanArray[$key] = $value;
            }
        }

        return $cleanArray;
    }


    /**
     * Realiza la conversión de valores en un arreglo para incluir comillas o 'null' según sea necesario.
     *
     * @param array $array El arreglo en el cual se realizará la conversión.
     * @param bool $removeTag Indica si se debe eliminar el tag <SQL> si está presente en los valores.
     * @return array El arreglo modificado con valores convertidos y listos para usar en consultas SQL.

     */
    static public function putQuoteAndNull($array, $removeTag = self::REMOVE_TAG ): array
    {
        //si hay registros
        if(is_array($array) && count($array)>0){

            //para cada elemento
            foreach ($array as $key => $value) {
                $array[$key] = self::putQuoteAndNullSingleValue($value, $removeTag);

            }
        }

        //retorna los datos trabajados
        return $array;
    }

    /**
     * Construye una cadena de filtro SQL a partir de un arreglo de filtros.
     *
     * @param array $filterArray El arreglo de filtros a convertir en condición SQL.
     * @param string $join El operador de unión para combinar múltiples condiciones. Valor predeterminado: AND.
     * @return string|null La cadena de filtro SQL resultante o null si el arreglo de filtros está vacío.
     */
    static public function getSQLFilter($filterArray, string $join = self::AND_JOIN): ?string
    {

        //pone datos nulos y comillas
        //$searchArray = self::putQuoteAndNull($filterArray,self::NO_REMOVE_TAG);
        $searchArray = $filterArray;

        //inicializa el campo que será devuelto
        $campos = array();

        //Si el arreglo de filtros no está vacío
        if(count($filterArray)>0){

            //para cara elemento, ya escapado
            foreach ($searchArray as $key => $value) {
                //si no tiene las comillas las pone
                if (strpos($key, '.') === false && strpos($key, '`') === false) {
                    $key = "`" . $key .  "`";
                }

                //Si el elemento no es nulo
                if($value != null){

                    //si es un arreglo genera un IN
                    if(is_array($value)){

                        // Si es un arreglo y contiene exactamente 2 elementos
                        if (count($value) === 2 && str_contains(strtoupper($key), 'BETWEEN')) {
                            // Si el arreglo tiene un identificador BETWEEN
                            $key = str_replace("BETWEEN", "", $key); // Elimina el identificador
                            $campos[] = "$key BETWEEN " . $value[0] . " AND " . $value[1];
                        } else {
                            //Une los valores del array y los separa por comas
                            $value = implode(" ,", $value);

                            //si no hay negacion
                            if (strpos($key, "!") === false) {
                                //almacena el filtro IN
                                $campos[] = "$key IN(" . $value . ") ";
                            } else {
                                $key = str_replace("!", "", $key);

                                //almacena el filtro IN
                                $campos[] = "$key NOT IN(" . $value . ") ";
                            }

                        }

                        //Si no es un arreglo
                    }else{

                        //Si el elemento contiene el tag <SQL>
                        if(substr_count($value, self::$SQL_TAG) > 0){


                            //Elimina el tag <SQL>
                            $value = str_replace(self::$SQL_TAG, "", $value);

                            $campos[] = "$key $value";

                            //Elemente no tiene tag <SQL>
                        }else{
                            if($value == "null"){
                                $campos[] = "$key IS NULL";
                            }else{

                                //si no hay porcentaje
                                if(strpos($value, "%") === false){

                                    //si no hay negacion
                                    if(strpos($key, "!") === false){

                                        //usa igual
                                        $campos[] = "$key=".$value;
                                    }else{
                                        $key = str_replace("!", "", $key);

                                        //usa distinto
                                        $campos[] = "$key <> ".$value;



                                    }

                                }else{

                                    //si hay porcentaje usa like
                                    $campos[] = "$key LIKE ".$value;
                                }

                            }
                        }
                    }
                }
            }

        }

        if(count($campos) > 0){
            $campos = implode($join, $campos);
            return " (" . $campos . ") ";
        }else{
            return null;
        }
    }

    /**
     * Inicia una transacción en la base de datos.
     *
     * @param string $conectionName El nombre de la conexión. Si no se proporciona, se utilizará la conexión por defecto.
     * @return void
     * @throws Exception
     */
    static public function StartTransaction($conectionName=null): void
    {
        $sql = "START TRANSACTION";
        self::execQuery($sql, false,false,$conectionName);
    }

    /**
     * Confirma (hace commit) una transacción en la base de datos.
     *
     * @param string $conectionName El nombre de la conexión. Si no se proporciona, se utilizará la conexión por defecto.
     * @return void
     * @throws Exception
     */
    static public function CommitTransaction($conectionName=null): void
    {
        $sql = "COMMIT";
        self::execQuery($sql, false,false,$conectionName);
    }

    /**
     * Realiza un rollback de una transacción en la base de datos.
     *
     * @param string $conectionName El nombre de la conexión. Si no se proporciona, se utilizará la conexión por defecto.
     * @return void
     * @throws Exception
     */
    static public function RollBackTransaction($conectionName=null): void
    {
        $sql = "ROLLBACK";
        self::execQuery($sql, false,false,$conectionName);
    }

    /**
     * Realiza una inserción de datos en la base de datos en la tabla especificada.
     *
     * @param string $table El nombre de la tabla en la que se realizará la inserción.
     * @param array $searchArray Un arreglo asociativo que contiene los datos a insertar, donde las claves son los nombres de los campos y los valores son los valores a insertar.
     * @param string $conectionName El nombre de la conexión. Si no se proporciona, se utilizará la conexión por defecto.
     * @return QueryInfo Un objeto QueryInfo que contiene información sobre el resultado de la inserción.
     * @throws Exception
     */
    static public function &_insert($table, $searchArray, $conectionName= null){

        //Obtiene nombre de los campos
        $def=array_keys($searchArray);

        //Para cada campo
        for ($i=0; $i < count($def); $i++) {

            //Agrega comillas
            $def[$i] = "`" . $def[$i] . "`";
        }

        //genera insert
        $sql = "INSERT INTO `$table`(". implode(",", $def) . ") VALUES(" . implode(",", $searchArray) . ")";

        //ejecuta
        return self::execQuery($sql, false,false,$conectionName);
    }

    /**
     * Realiza una actualización de datos en la base de datos en la tabla especificada.
     *
     * @param string $table El nombre de la tabla en la que se realizará la actualización.
     * @param array $searchArray Un arreglo asociativo que contiene los datos a actualizar, donde las claves son los nombres de los campos y los valores son los nuevos valores.
     * @param array $condicion Un arreglo asociativo que especifica las condiciones para determinar qué registros serán actualizados. Las claves son los nombres de los campos y los valores son los valores que deben coincidir.
     * @param string $conectionName El nombre de la conexión. Si no se proporciona, se utilizará la conexión por defecto.
     * @return QueryInfo Un objeto QueryInfo que contiene información sobre el resultado de la actualización.
     * @throws Exception
     */
    static public function &_update($table, $searchArray, $condicion, $conectionName= null){
        $def=array_keys($searchArray);

        $sql = "UPDATE `$table` SET ";
        $total = count($searchArray);
        $x=0;
        foreach ($searchArray as $key => $value) {
            $sql .= "`$key` = $value";

            if($x < $total-1){

                $sql .= ", ";
                $x++;
            }
        }

        $sql .= " WHERE ";

        $sql .=  self::getSQLFilter($condicion);

        //echo $sql;
        return self::execQuery($sql, false,false,$conectionName);
    }

    /**
     * Realiza una eliminación de registros en la base de datos en la tabla especificada.
     *
     * @param string $table El nombre de la tabla de la que se eliminarán registros.
     * @param array $condicion Un arreglo asociativo que especifica las condiciones para determinar qué registros serán eliminados. Las claves son los nombres de los campos y los valores son los valores que deben coincidir.
     * @param string $conectionName El nombre de la conexión. Si no se proporciona, se utilizará la conexión por defecto.
     * @return QueryInfo Un objeto QueryInfo que contiene información sobre el resultado de la eliminación.
     * @throws Exception
     */
    static public function &_delete($table, $condicion, $conectionName= null){

        $sql = "DELETE FROM `$table` ";


        $sql .= " WHERE ";

        $sql .=  self::getSQLFilter($condicion);

        //echo $sql;
        return self::execQuery($sql, false,false,$conectionName);
    }

    /**
     * Mapea un arreglo de datos a un nuevo arreglo siguiendo un mapa específico de campos.
     *
     * @param array $prototype El arreglo de datos original que se mapeará.
     * @param array $map Un arreglo asociativo que representa el mapa de campos. Las claves son los nombres de los campos en el arreglo original, y los valores son los nombres de los campos en el nuevo arreglo.
     * @param bool $map_nulls Indica si los campos nulos también deben ser mapeados. Si es `true`, los campos nulos también se incluirán en el nuevo arreglo.
     * @return array El arreglo mapeado resultante con los campos según el mapa especificado.
     */
    static public function mapToBd($prototype, $map, $map_nulls = false): array
    {

        $searchArray = array();
        foreach ($map as $key => $value) {

            if(isset($prototype[$key]) ||
                ($map_nulls && array_key_exists($key, $prototype))
            ){
                $searchArray[$value] = $prototype[$key];
            }
        }


        return $searchArray;
    }

    /**
     * Agrega paginación al SQL proporcionado.
     *
     * @param string $sql El SQL al que se agregará la paginación.
     * @return string El SQL modificado con la paginación añadida.
     */
    static protected function addPagination(string $sql, QueryParams $qSettings): string
    {

        //agrega limit si page es un numero mayor a cero
        if($qSettings->isEnablePaging() ){

            $page = $qSettings->getPage();
            if($page >= 0){
                if($qSettings->getPaginationMode() == PaginationMode::SQL_CALC_FOUND_ROWS){
                    //agrega SQL_CALC_FOUND_ROWS al query
                    $sql = trim($sql);
                    $sql = str_replace("\n", " ", $sql);
                    $exploded = explode(" ", $sql);
                    $exploded[0] .= " SQL_CALC_FOUND_ROWS ";
                    $sql = implode(" ", $exploded);
                }



                $desde = ($page) * $qSettings->getCantByPage();
                $sql_pagination = " LIMIT $desde, " . $qSettings->getCantByPage();
                $sql = self::embedParams($sql, $qSettings->getPaginationReplaceTag(), $sql_pagination);
            }

        }
        return $sql;
    }

    /**
     * Agrega ordenamiento a la consulta SQL proporcionada.
     *
     * @param string $sql El SQL al que se agregará el ordenamiento.
     * @return string El SQL modificado con el ordenamiento añadido.
     */
    static protected function addOrder(string $sql, QueryParams $qSettings): string
    {
        $field = $qSettings->getOrderFields();
        $val = null;

        //agrega SQL_CALC_FOUND_ROWS al query
        $sql = trim($sql);

        if(count($field) > 0){
            $all_orders = array();
            foreach ($field as $order_name => $order_type) {

                //if(self::validFieldExist($order_name, $sql)){

                if(!str_contains($order_name, "`" ) && !str_contains($order_name, "." )) {
                    $order_name = "`$order_name`";
                }
                $all_orders[] = $order_name . " " . $order_type;
                //}
            }
            $val = " ORDER BY " . implode(",", $all_orders);
        }
        $sql = self::embedParams($sql, $qSettings->getOrderReplaceTag(), $val);

        return $sql;
    }

    /**
     * Incrusta un valor en el SQL en los lugares marcados por un tag específico.
     *
     * @param string $sql El SQL en el que se incrustará el valor.
     * @param string $tag El tag que marca los lugares donde se incrustará el valor.
     * @param mixed $value El valor que se incrustará en los lugares marcados por el tag.
     * @return string El SQL modificado con el valor incrustado en los lugares indicados por el tag.
     */
    static protected  function embedParams($sql, $tag, $value){

        $pattern = "/\{(.+)\}/";

        preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE);

        for($i=0; $i < count($matches[0]); $i++){
            $foundKey = $matches[1][$i][0];

            $data_array = explode(" ", $foundKey);

            if(isset($data_array[0]) && $data_array[0] == $tag){

                if($value){
                    $replaceWith = $value;
                }else{
                    $replaceWith = $foundKey;
                }


                $sql = str_replace("{".$foundKey."}", $replaceWith, $sql);
            }


        }
//			var_dump($matches);
        if(count($matches[0]) == 0){
            $sql .= $value;
        }

        return $sql;
    }


    /**
     * Agrega filtros al SQL según los parámetros de filtrado proporcionados en la solicitud.
     *
     * @param string $sql El SQL al que se agregarán los filtros.
     * @return string El SQL modificado con los filtros agregados.
     * @throws Exception
     */
    static protected function addFilters(string $sql, QueryParams $qSettings): string
    {
        $filters = $qSettings->getFilterString();
        $columns = $qSettings->getFilterColumns();

        if($filters){
            $filters = explode(" ", $filters);
            $filters_checked = [];
            $real_query_fields = [];
            if($qSettings->getFiltersCheckMode() == FiltersCheckMode::PRELOAD_FIELDS) {
                $sql_template = str_replace("SQL_CALC_FOUND_ROWS", "", $sql);
                $sql_template .= " LIMIT 0";
                $s = self::execQuery($sql_template);
                $real_query_fields = self::getFieldLabels($s);
                foreach ($columns as $key => $filter_column) {
                    if(in_array($filter_column, $real_query_fields)){
                        $filters_checked[] = "`" . $filter_column . "` LIKE '%%'";
                    }
                }

            }else if ($qSettings->getFiltersCheckMode() == FiltersCheckMode::CHECK_IN_QUERY){
                for($x=0; $x < count($columns); $x++){
                    //echo $x . " ";
                    if(empty($columns[$x]) || !strpos($sql, $columns[$x])){
                        continue;
                    }
                    $real_query_fields[] = $columns[$x];
                    $filters_checked[] = "`" . $columns[$x] . "` LIKE '%%'";
                }
            }else{
                $real_query_fields = $columns;
            }

            $sql_filter = implode(" OR ", $filters_checked);
            $sql_filter = "($sql_filter)";

            $all_filters = array();
            foreach ($filters as $text) {
                $advance = explode("::", $text);

                //si son tres textos separados por dos puntos y el primer texto está en el query
                if(count($advance) == 3 && in_array($advance[0], $real_query_fields) ){

                    $advance[2] = str_replace(';;', ' ', $advance[2]);

                    $val_org = $advance[2];

                    if(validDate($advance[2])){
                        $advance[0] = "STR_TO_DATE($advance[0],'".Environment::$DB_DISPLAY_DATE_FORMAT."')";
                        $advance[2] = "STR_TO_DATE('{$advance[2]}','".Environment::$DB_DISPLAY_DATE_FORMAT."')";
                    }else{
                        $advance[2] = "'" . self::escape($advance[2]) . "'";
                    }

                    switch ($advance[1]) {
                        case 'eq':
                            $all_filters[] = $advance[0] . " = " . $advance[2] ;
                            break;

                        case 'ne':
                            $all_filters[] = $advance[0] . " <> " . $advance[2] ;
                            break;

                        case 'lk':
                            $all_filters[] = $advance[0] . " LIKE '%" . $val_org  . "%'";
                            break;

                        case 'gt':
                            $all_filters[] = $advance[0] . " > " . $advance[2] ;
                            break;

                        case 'ge':
                            $all_filters[] = $advance[0] . " >= " . $advance[2] ;
                            break;

                        case 'lt':
                            $all_filters[] = $advance[0] . " < " . $advance[2]  ;
                            break;

                        case 'le':
                            $all_filters[] = $advance[0] . " <= " . $advance[2]  ;
                            break;

                        case 'be':
                            $advance[2] = str_replace("'", "", $advance[2]);
                            $fx = explode(",",$advance[2]);
                            if(count($fx) >= 2){
                                $all_filters[] = $advance[0] . " BETWEEN '" . $fx[0] . "' AND '" . $fx[1] ."'";
                            }
                            break;
                    }
                }else{
                    $all_filters[] = str_replace("%%", "%$text%", $sql_filter);
                }
            }
            $all_filters = implode(" AND ", $all_filters);


            $sql .= " " . $qSettings->getHavingUnion() . "  $all_filters ";
        }

        return $sql;
    }

    /**
     * Agrega una cláusula GROUP BY al SQL basado en los parámetros de agrupación proporcionados en la solicitud.
     *
     * @param string $sql El SQL al que se agregará la cláusula GROUP BY.
     * @return string El SQL modificado con la cláusula GROUP BY agregada.
     */
    static protected function addGroups($sql): string
    {
        $groups = Handler::getRequestAttr("GROUPS");


        if($groups){
            $columns = explode(",", $groups);

            $sql_groups = array();
            //echo var_dump($columns);
            for($x=0; $x < count($columns); $x++){
                //echo $x . " ";
                if(!strpos($sql, $columns[$x])){
                    continue;
                }

                $sql_groups[] = $columns[$x];
            }
            $sql_groups = implode(", ", $sql_groups);

            $sql .= " GROUP BY $sql_groups";
        }

        return $sql;
    }

    static protected function getConfigs($orderField, $asc=true, $page=-1, $limitPerPage=0, $groupDefault=null): void
    {
        //inyecta en el post valores para agregar paginación y ordenado

        if(!isset($_POST['PAGE'])){
            $_POST['FIELD'] = $orderField;
            $_POST['ASC'] = $asc;
            $_POST['PAGE'] = $page;
        }

        if(!isset($_POST['GROUPS'])){
            $_POST['GROUPS'] = $groupDefault;
        }

    }

    /**
     * Devuelve una expresión SQL para formatear un campo de fecha y hora en el formato "dd-mm-yyyy hh:mm:ss AM/PM".
     *
     * @param string $field El nombre del campo de fecha y hora en la base de datos.
     * @return string Una expresión SQL que formatea el campo de fecha y hora en el formato especificado.
     */
    static public function getDateTimeFormat($field): string
    {
        return " DATE_FORMAT($field,'%d-%m-%Y %h:%i:%s %p') ";
    }

    /**
     * Devuelve una expresión SQL para formatear un campo de fecha en el formato "dd-mm-yyyy".
     *
     * @param string $field El nombre del campo de fecha en la base de datos.
     * @return string Una expresión SQL que formatea el campo de fecha en el formato especificado.
     */
    static public function getDateFormat($field): string
    {
        return " DATE_FORMAT($field,'%d-%m-%Y') ";
    }

    /**
     * Devuelve una expresión SQL para formatear un campo de tiempo en el formato "hh:mm:ss AM/PM".
     *
     * @param string $field El nombre del campo de tiempo en la base de datos.
     * @return string Una expresión SQL que formatea el campo de tiempo en el formato especificado.
     */
    static public function getTimeFormat($field): string
    {
        return " DATE_FORMAT($field,'%h:%i:%s %p') ";
    }

    /**
     * Devuelve una expresión SQL para obtener la hora de un campo de fecha y hora en el formato de 24 horas.
     *
     * @param string $field El nombre del campo de fecha y hora en la base de datos.
     * @return string Una expresión SQL que obtiene la hora del campo de fecha y hora en el formato de 24 horas.
     */
    static public function getHourFormat($field): string
    {
        return " DATE_FORMAT($field,'%H') ";
    }

    /**
     * Obtiene el número de campos en el conjunto de resultados de una consulta.
     *
     * @param QueryInfo $sumary El objeto QueryInfo que contiene el conjunto de resultados de la consulta.
     * @return int El número de campos en el conjunto de resultados.
     */
    static public function getNumFields(QueryInfo &$sumary): int
    {
        return mysqli_num_fields($sumary->result);
    }

    /**
     * Obtiene información sobre un campo específico en el conjunto de resultados de la consulta.
     *
     * @param QueryInfo $sumary El objeto QueryInfo que contiene el conjunto de resultados de la consulta.
     * @param int $i El índice del campo para el que se desea obtener información (comenzando desde 0).
     * @return object|bool Un objeto que contiene información sobre el campo, o false si no se puede obtener la información.
     */
    static public function getFieldInfo(QueryInfo &$sumary, $i): object|bool
    {
        return mysqli_fetch_field_direct($sumary->result, $i);
    }

    /**
     * Obtiene el tipo de datos del campo en el conjunto de resultados de la consulta.
     *
     * @param QueryInfo $sumary El objeto QueryInfo que contiene el conjunto de resultados de la consulta.
     * @param int $i El índice del campo para el que se desea obtener el tipo de datos (comenzando desde 0).
     * @return int El tipo de datos del campo.
     */
    static public function getFieldType(QueryInfo &$sumary, $i){

        $info_campo = self::getFieldInfo($sumary, $i);
        return $info_campo->type;
    }

    /**
     * Obtiene la longitud máxima del campo en el conjunto de resultados de la consulta.
     *
     * @param QueryInfo $sumary El objeto QueryInfo que contiene el conjunto de resultados de la consulta.
     * @param int $i El índice del campo para el que se desea obtener la longitud (comenzando desde 0).
     * @return int La longitud máxima del campo.
     */
    static public function getFieldLen(QueryInfo &$sumary, $i){
        $info_campo = self::getFieldInfo($sumary, $i);
        return $info_campo->max_length;
    }

    /**
     * Obtiene las banderas del campo en forma de array binario.
     *
     * @param QueryInfo $sumary El objeto QueryInfo que contiene el conjunto de resultados de la consulta.
     * @param int $i El índice del campo para el que se desean obtener las banderas (comenzando desde 0).
     * @return array Un array de banderas del campo en forma de valores binarios.
     */
    static public function getFieldFlagsBin(QueryInfo &$sumary, $i): array
    {
        $info_campo = self::getFieldInfo($sumary, $i);

        //convierte a binario, invierte y divide de uno en uno
        $bin_flags = str_split(strrev(decbin($info_campo->flags)),1);




        return $bin_flags;
    }

    /**
     * Obtiene las banderas del campo en forma de cadena de texto.
     *
     * @param QueryInfo $sumary El objeto QueryInfo que contiene el conjunto de resultados de la consulta.
     * @param int $i El índice del campo para el que se desean obtener las banderas (comenzando desde 0).
     * @return string Una cadena de texto que representa las banderas del campo.
     */
    static public function getFieldFlags(QueryInfo &$sumary, $i): string
    {


        //convierte a binario, invierte y divide de uno en uno
        $bin_flags = self::getFieldFlagsBin($sumary, $i);

        $flags = array();

        if($bin_flags[0] == 1){
            $flags[] = "not_null";
        }


        return implode(" ", $flags);
    }


    /**
     * Activa la función de escape de caracteres HTML para los datos.
     */
    static public function escaoeHTML_ON(){
        self::$escapeHTML=true;
    }

    /**
     * Desactiva la función de escape de caracteres HTML para los datos.
     */
    static public function escaoeHTML_OFF(){
        self::$escapeHTML=false;
    }

    /**
     * Escapa caracteres especiales a entidades HTML en los datos si la función de escape HTML está activada.
     *
     * @param mixed $data Los datos que se desean escapar.
     * @return mixed Los datos escapados con caracteres HTML.
     */
    static public function escape_HTML($data){

        if(self::$escapeHTML && is_array($data)){
            foreach ($data as $key => $value) {
                $data[$key] = htmlspecialchars($value, ENT_QUOTES , "UTF-8");
            }
        }

        return $data;
    }

    /**
     * Reinicia el puntero del conjunto de resultados a una posición específica.
     *
     * @param QueryInfo $sumary El objeto QueryInfo que contiene el conjunto de resultados de la consulta.
     * @param int $pos La posición a la que se desea mover el puntero del conjunto de resultados.
     * @return bool Devuelve true si el puntero se movió correctamente, o false si no se pudo mover.
     */
    function resetPointer(QueryInfo &$sumary, $pos = 0): bool
    {
        $status = false;

        if($sumary->allRows > 0){
            $status = mysqli_data_seek( $sumary->result , $pos);
        }
        return $status;
    }

    /**
     * Agrega comillas (`) alrededor de los nombres de campo en un array.
     *
     * @param array $fields Los nombres de campo a los que se les desea agregar comillas.
     * @return array Un array con los nombres de campo rodeados por comillas (`).
     */
    public static function quoteFieldNames($fields): array
    {
        $all = array();
        foreach ($fields as $key => $name) {
            //solo agrega las comillas si no la encuentra
            if (strpos($name, '`') === false) {
                $all[] = "`" . $name .  "`";
            }

        }

        return $all;
    }

    /**
     * Devuelve una cadena que representa la función SQL NOW() para usar en consultas.
     *
     * @return string Una cadena que representa la función SQL NOW().
     */
    public static function valueNOW(): string
    {
        return self::$SQL_TAG . " NOW() ";
    }

    /**
     * Devuelve una cadena que representa la condición SQL IS NULL para usar en consultas.
     *
     * @return string Una cadena que representa la condición SQL IS NULL.
     */
    public static function valueISNULL(): string
    {
        return self::$SQL_TAG . " IS NULL ";
    }

    /**
     * Verifica si un campo específico existe en una consulta SQL.
     *
     * @param string $field El nombre del campo que se desea verificar.
     * @param string $sql La consulta SQL en la que se buscará el campo.
     * @return bool Devuelve true si el campo existe en la consulta, o false si no existe.
     */
    public static function validFieldExist($field, $sql): bool
    {
        $valid = false;

        if(strpos($sql, $field)){
            $valid = true;
        }

        return $valid;
    }

    /**
     * Almacena un registro de consulta SQL en el log de depuración, si está habilitado.
     *
     * @param mixed $connectionName La conexión de base de datos donde se almacenará el registro.
     * @param string $sql La consulta SQL que se almacenará en el log.
     * @return void
     */
    public static function storeDebugLog($connectionName, $sql): void
    {

        if(self::$enableDebugLog){
            $sql = self::escape($sql);

            //genera insert
            $sql_ins = "INSERT INTO ".self::$debugTable."(date,exec_sql,tag) VALUES( NOW(), '$sql', '".self::$debugTAG."' )";

            @mysqli_query($connectionName, $sql_ins);
        }
    }

    /**
     * Habilita el registro de consultas SQL en el log de depuración.
     *
     * @param string $tag Un tag opcional para identificar el registro en el log.
     * @return void
     */
    public static function enableDebugLog($tag=''): void
    {
        self::$enableDebugLog = true;
        self::$debugTAG = $tag;
    }

    /**
     * Deshabilita el registro de consultas SQL en el log de depuración.
     *
     * @return void
     */
    public static function disableDebugLog(): void
    {
        self::$enableDebugLog = FALSE;
        self::$debugTAG = "";
    }

    /**
     * Almacena una variable de datos en el almacén de variables estáticas.
     *
     * @param string $key La clave bajo la cual se almacenará la variable.
     * @param mixed $value El valor de la variable a almacenar.
     * @return void
     */
    public static function setDataVar($key, $value): void
    {
        self::$_vars[$key] = $value;
    }

    /**
     * Obtiene el valor de una variable de datos almacenada en el almacén de variables estáticas.
     *
     * @param string $key La clave de la variable que se desea obtener.
     * @return mixed|null El valor de la variable si existe, o null si no existe.
     */
    public static function getDataVar($key){
        $value = null;

        if(isset(self::$_vars[$key])){
            $value = self::$_vars[$key];
        }

        return $value;
    }

    /**
     * Deshabilita temporalmente la verificación de claves foráneas en la base de datos.
     *
     * @param string|null $connectionName El nombre de la conexión de base de datos, si se proporciona.
     * @return bool Devuelve true si la operación se realizó con éxito, o false en caso contrario.
     * @throws Exception
     */
    public static function disableForeignKeyCheck($connectionName=null): bool
    {
        $sql = "SET foreign_key_checks = 0";

        return self::execNoQuery($sql, $connectionName);
    }

    /**
     * Habilita la verificación de claves foráneas en la base de datos.
     *
     * @param string|null $connectionName El nombre de la conexión de base de datos, si se proporciona.
     * @return bool Devuelve true si la operación se realizó con éxito, o false en caso contrario.
     * @throws Exception
     */
    public static function enableForeignKeyCheck($connectionName=null): bool
    {
        $sql = "SET foreign_key_checks = 1";

        return self::execNoQuery($sql, $connectionName);
    }

    /**
     * Obtiene los datos de conexión para una conexión específica.
     *
     * @param string|null $connectionName El nombre de la conexión de base de datos, si se proporciona.
     * @return Connection El objeto de conexión correspondiente.
     * @throws Exception
     */
    static function getConnectionData(?string $connectionName= null): Connection
    {
        $conn = null;

        if(empty($connectionName) || !isset(self::$conections[$connectionName])){
            if(empty(self::$defaultConection)){
                throw new Exception("No default connection set");
            }
            $connectionName = self::$defaultConection;
        }

        return self::$conections[$connectionName];
    }

    static public function getFieldLabels(QueryInfo $sumary): array
    {
        $finfo = mysqli_fetch_fields($sumary->result);

        $all = [];
        foreach ($finfo as $val) {
            $all[] = $val->name;
        }

        return $all;
    }
}
