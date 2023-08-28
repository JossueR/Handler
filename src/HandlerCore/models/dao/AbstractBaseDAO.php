<?php
namespace HandlerCore\models\dao;
use Exception;
use HandlerCore\Environment;
use HandlerCore\models\QueryInfo;
use HandlerCore\models\SimpleDAO;

/**
 * Clase base para los Data Access Objects (DAO).
 * Extiende la clase SimpleDAO y proporciona métodos y propiedades adicionales para interactuar con una tabla de la base de datos.
 */
class AbstractBaseDAO extends SimpleDAO {
    /** @var QueryInfo Información del resultado de la consulta */
    protected $sumary;

    /** @var bool Indicador de autoconfigurabilidad de consultas */
    public $autoconfigurable = false;

    /** @var string|null ID para selección */
    public $selectID;

    /** @var string|null Nombre para selección */
    public $selectName;

    /** @var array Array de errores */
    public $errors;

    /** @var string Descripción del registro en el registro de actividad */
    public $logDesc;

    /** @var array Mapa de mapeo de campos */
    protected $map;

    /** @var array Prototipo de datos */
    protected $prototype;

    /** @var string Consulta base para selección */
    protected $baseSelect;

    /** @var string|null Nombre de la conexión de base de datos */
    protected $conectionName;

    /** @var bool Habilita el registro de historial */
    protected $enableHistory;

    /** @var string Nombre de la tabla de historial */
    protected $historyTable;

    /** @var bool|array Mapa de mapeo de historial */
    protected $historyMap = false;

    /** @var string|null Última consulta de selección ejecutada */
    private $lastSelectQuery;

    /** @var bool Indicador de ejecución de búsqueda */
    private $execFind;

    /** @var array Información de campos */
    private $fields_info;

    /** @var array|null Cache para almacenar datos temporales */
    private static $cache;

    /**
     * Constructor de la clase AbstractBaseDAO.
     *
     * @param string $tabla Nombre de la tabla en la base de datos.
     * @param string $id ID de la tabla.
     * @param string $baseSelect Consulta base para selección.
     * @param array $map Mapa de mapeo de campos.
     * @param array $prototype Prototipo de datos.
     */
    function __construct($tabla, $id, $baseSelect='', $map='', $prototype='') {
        parent::__construct($tabla, $id);
        $this->baseSelect= $baseSelect;
        $this->map= $map;
        $this->prototype = $prototype;
        $this->execFind =true;
    }

    /**
     * Establece la configuración para el registro de historial.
     *
     * @param string $table Nombre de la tabla de historial.
     * @param array $map Mapa de mapeo de campos para el historial.
     * @return void
     */
    function setHistory($table, $map){
        $this->enableHistory = true;

        $this->historyTable=$table;
        $this->historyMap=$map;
    }

    /**
     * Obtiene la información del resultado de la consulta.
     *
     * @return QueryInfo Información del resultado de la consulta.
     */
    function &getSumary(): QueryInfo
    {
        return $this->sumary;
    }

    /**
     * Inserta un nuevo registro en la tabla de la base de datos.
     *
     * @param array $searchArray Datos a insertar.
     * @return QueryInfo Información del resultado de la consulta.
     * @throws Exception
     */
    function &insert($searchArray){
        $this->sumary = parent::_insert(parent::getTableName(), $searchArray,$this->conectionName);
        $this->_recordLog(array("Action" => "INSERT"));
        $this->_history($searchArray);
        return $this->sumary;

    }

    /**
     * Actualiza registros en la tabla de la base de datos.
     *
     * @param array $searchArray Datos a actualizar.
     * @param array $condicion Condiciones para la actualización.
     * @return QueryInfo Información del resultado de la consulta.
     * @throws Exception
     */
    function &update($searchArray, $condicion){

        $this->sumary = parent::_update(parent::getTableName(), $searchArray, $condicion,$this->conectionName);
        $this->_recordLog(array("Action" => "UPDATE"));
        //Update no hace history por que podria no estar actualizando algo solo por id, sino multiples registros
        return $this->sumary;

    }

    /**
     * Elimina registros de la tabla de la base de datos.
     *
     * @param array $prototype Prototipo para la eliminación.
     * @return QueryInfo Información del resultado de la consulta.
     * @throws Exception
     */
    function &delete($prototype){
        $condicion = parent::mapToBd($prototype, $this->getDBMap());
        $condicion = parent::putQuoteAndNull($condicion, !self::REMOVE_TAG);

        $this->sumary= parent::_delete(parent::getTableName(), $condicion,$this->conectionName);
        $this->_recordLog(array("Action" => "DELETE"));
        //$this->_history($searchArray);
        return $this->sumary;

    }

    /**
     * Elimina un registro por su ID en la tabla de la base de datos.
     *
     * @param array $prototype Prototipo que contiene el ID del registro a eliminar.
     * @return bool `true` si se eliminó al menos un registro, `false` si no se eliminó ningún registro.
     * @throws Exception
     */
    function deleteByID($prototype): bool
    {
        $searchArray = parent::mapToBd($prototype, $this->getDBMap());
        $condicion = $this->getIdFromDBMap($searchArray);
        $condicion = parent::putQuoteAndNull($condicion);
        $this->sumary = parent::_delete(parent::getTableName(), $condicion,$this->conectionName);

        return $this->sumary->total > 0;
    }

    /**
     * Verifica si existe un registro en la tabla de la base de datos basado en su ID.
     *
     * @param array $searchArray Prototipo que contiene el ID a buscar.
     * @return bool `true` si existe un registro con el ID proporcionado, `false` si no existe.
     * @throws Exception
     */
    function exist($searchArray): bool
    {
        $searchArray = $this->getIdFromDBMap($searchArray);
        $filters = parent::getSQLFilter($searchArray);

        if($filters){
            $sql = /** @lang text */
                "SELECT COUNT(*) FROM `" . parent::getTableName() . "` WHERE " . $filters;
            return parent::execAndFetch($sql,$this->conectionName) > 0;
        }else{
            return false;
        }
    }

    /**
     * Verifica si existe un registro en la tabla de la base de datos basado en un conjunto de condiciones.
     *
     * @param array $searchArray Condiciones para la búsqueda.
     * @param bool $escape Determina si se deben escapar los valores en las condiciones.
     * @return bool `true` si existe un registro que cumple con las condiciones, `false` si no existe.
     * @throws Exception
     */
    function existBy($searchArray, $escape=false): bool
    {
        if($escape){
            $searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
        }

        $sql = "SELECT COUNT(*) FROM " . parent::getTableName() . " WHERE " . parent::getSQLFilter($searchArray);
        return parent::execAndFetch($sql,$this->conectionName) > 0;
    }

    /**
     * Obtiene un arreglo que contiene los campos de identificación (ID) y sus valores correspondientes
     * en el formato adecuado para ser utilizado en la búsqueda por ID.
     *
     * @param array $searchArray Arreglo con los valores de identificación.
     * @return array Arreglo de condiciones para la búsqueda por ID.
     */
    function getIdFromDBMap($searchArray): array
    {
        $condicion = array();

        foreach (parent::getId() as $key ) {
            $condicion[parent::getTableName() . "." . $key] = (isset($searchArray[$key]))? $searchArray[$key] : null;
        }

        return $condicion;
    }

    /**
     * Obtiene el número total de registros devueltos por la última consulta realizada.
     *
     * @return int Número total de registros.
     */
    function getTotals(): int
    {
        return $this->sumary->total;
    }

    /**
     * Obtiene un arreglo con los nombres de los campos presentes en el resultado de la última consulta.
     *
     * @return array Arreglo de nombres de campos.
     */
    function getFields(): array
    {
        $fields = array();

        if($this->sumary->result){
            $total = mysqli_num_fields($this->sumary->result);

            for ($i=0; $i < $total; $i++) {
                $info_campo = mysqli_fetch_field_direct($this->sumary->result, $i);
                $fields[] = $info_campo->name;
            }
        }


        return $fields;
    }


    /**
     * Guarda un registro en la base de datos. Si el registro ya existe, se actualiza; de lo contrario, se inserta.
     *
     * @param array $prototype Arreglo con los datos del registro a guardar.
     * @param int $update Opción de actualización: 0 (INSERT), 1 (UPDATE), o 2 (automático).
     * @return bool Verdadero si el guardado es exitoso, falso en caso contrario.
     * @throws Exception
     */
    public function save($prototype, $update=2): bool
    {

        $searchArray = parent::mapToBd($prototype, $this->getDBMap());

        if(!$this->validate($searchArray)){
            return false;
        }


        $searchArray = parent::putQuoteAndNull($searchArray);

        switch ($update) {
            case parent::INSERT:
            case parent::EDIT:

                break;

            default:

                $update = ($this->exist($searchArray))? parent::EDIT : parent::INSERT;
        }

        if($update === parent::INSERT ){

            $this->sumary = $this->insert($searchArray);

        }else{
            $condicion = array();

            foreach (parent::getId() as $key ) {
                $condicion[$key] = $searchArray[$key];
                unset($searchArray[$key]);
            }
            $this->sumary = $this->update($searchArray, $condicion);
            $this->_history(array_merge($searchArray,$condicion));
        }

        if($this->sumary->errorNo != 0){
            $this->errors["#DB"] = $this->sumary->error;
        }

        return ($this->sumary->errorNo == 0);
    }

    /**
     * Realiza una consulta personalizada en la base de datos y guarda el resultado en la propiedad $sumary.
     *
     * @param string $sql Consulta SQL personalizada.
     * @return void
     * @throws Exception
     */
    public function find($sql){
        $this->lastSelectQuery = $sql;

        if($this->execFind){
            $this->sumary = parent::execQuery($sql, true, $this->autoconfigurable,$this->conectionName);
        }else{
            //habilita la ejecucion del query
            $this->enableExecFind();
        }

    }


    /**
     * Obtiene el siguiente registro del resultado de la última consulta realizada.
     *
     * @return array|false Arreglo con los datos del siguiente registro o falso si no hay más registros.
     */
    public function get(): bool|array
    {
        if($this->sumary->result){
            return parent::getNext($this->sumary);
        }else{
            return false;
        }
    }

    /**
     * Obtiene todos los registros del resultado de la última consulta realizada.
     *
     * @return array|false Arreglo de arreglos con los datos de todos los registros o falso si no hay registros.
     */
    public function fetchAll(): bool|array
    {
        if($this->sumary->result){
            return parent::getAll($this->sumary);
        }else{
            return false;
        }
    }


    /**
     * Realiza una consulta para obtener registros en base a un arreglo de condiciones.
     *
     * @param array $proto Arreglo con las condiciones para la consulta.
     * @return void
     * @throws Exception
     */
    public function getBy($proto){
        $searchArray = parent::mapToBd($proto, $this->getDBMap());

        $temp = array();
        foreach ($searchArray as $key => $value) {
            if (strpos($key, '.') === false){
                $temp[$this->tableName . "." . $key] = $value;
            }

        }
        $searchArray = $temp;

        $searchArray = parent::putQuoteAndNull($searchArray);
        $sql_where = $this->getSQLFilter($searchArray);

        $sql = $this->getBaseSelec();
        $sql .= " $sql_where";

        $this->find($sql);
    }


    /**
     * Realiza una consulta para obtener un registro por su identificador.
     *
     * @param array $proto Arreglo con el identificador del registro a buscar.
     * @param bool $use_cashe Determina si se debe utilizar la caché para registros previamente buscados.
     * @return void
     * @throws Exception
     */
    public function getById($proto, $use_cashe = true){
        $protoDB = parent::mapToBd($proto, $this->getDBMap());
        $searchArray = $this->getIdFromDBMap($protoDB);

        $searchArray = parent::putQuoteAndNull($searchArray);
        $sql_where = $this->getSQLFilter($searchArray);

        $sql = $this->getBaseSelec();
        $sql .= " $sql_where";

        $classname = get_class($this);
        $cache = self::getCache($classname);

        //si ya existe y es el mismo y esta habilitado el uso de cache
        if($use_cashe && $cache && $cache->equals($proto)){
            //var_dump("ya existe $classname id:" . json_encode($proto));


            //establece el sumary del cashe
            $this->sumary = $cache->getSummary();

            //resetea el puntero
            $this->resetGetData();

        }else{
            //var_dump("busca primera ves $classname id:" . json_encode($proto));

            //busca
            $this->find($sql);

            $cache = new CasheFindData($proto, $this->sumary);

            //almacena en cashe
            self::$cache[$classname] = $cache;
        }

    }

    /**
     * Obtiene un prototipo lleno con datos recuperados de la última fila consultada.
     *
     * @param array|null $prototype Prototipo opcional para llenar con datos. Si no se proporciona, se utiliza el prototipo definido en la clase.
     * @return array Prototipo lleno con los datos de la última fila consultada.
     */
    public function getFilledPrototype($prototype = null){
        if(!$prototype){
            $prototype = $this->getPrototype();
        }

        $map = $this->getDBMap();
        $row_data = $this->get();

        if($row_data) {
            foreach ($prototype as $proto_key => $value) {
                if (isset($map[$proto_key])) {
                    $prototype[$proto_key] = $row_data[$map[$proto_key]];
                } else if (isset($row_data[$proto_key])) {
                    $prototype[$proto_key] = $row_data[$proto_key];
                }

            }
        }
        return $prototype;
    }


    /**
     * Válida los datos en el array proporcionado según las restricciones de la base de datos y define los errores encontrados.
     *
     * @param array $searchArray Array de datos a ser validados.
     * @return bool True si los datos son válidos según las restricciones, False si hay errores de validación.
     * @throws Exception
     */
    public function validate($searchArray): bool
    {
        $errors = array();

        $fields = array_keys($searchArray);
        $fields_all = implode(',', $this->quoteFieldNames($fields));
        $sql = "SELECT " . $fields_all . " FROM " . $this->tableName . " LIMIT 0";
        $summary = parent::execQuery($sql, true);

        $i = 0;
        $total = parent::getNumFields($summary);

        while ($i < $total) {
            $f = $fields[$i];
            $type = parent::getFieldType($summary, $i);
            $len = parent::getFieldLen($summary, $i);
            $flag = explode(" ", parent::getFieldFlags($summary, $i));

            //verifica requerido
            if(in_array("not_null", $flag)){

                if($searchArray[$f] === null || $searchArray[$f] === "null" || $searchArray[$f] === ""){
                    //error
                    $errors[$f] = "required";
                }

            }

            //verifica tipo
            if($type == "string"){

                //verifica maxlen
                if(strlen($searchArray[$f]) > ($len / 3)){
                    //error maxlen
                    $errors[$f] = "too_long";
                }

            }

            if($type == "int"){


                //verifica si es entero
                if(($searchArray[$f] != "" && !is_numeric($searchArray[$f])) || $searchArray[$f] - intval($searchArray[$f]) != 0){
                    //error no es numero entero
                    $errors[$f] = "no_int";
                }
            }

            if($type == "real"){
                //verifica si es real
                if( ($searchArray[$f] != "" && !is_numeric($searchArray[$f])) || floatval($searchArray[$f]) - $searchArray[$f] != 0 ){
                    //error no es numero real
                    $errors[$f] = "no_decimal";
                }
            }


            $i++;
        }


        $this->errors = $errors;
        return (count($errors) == 0);
    }

    /**
     * Retorna un arreglo asociativo donde las claves representan los campos que se buscarán en la solicitud (request)
     * y se cargarán automáticamente. Esto permite enmascarar los nombres reales de los campos de la base de datos.
     *
     * @return array Un arreglo asociativo con los campos y sus correspondientes nombres para búsqueda.
     */
    function getPrototype(){
        return $this->prototype;
    }

    /**
     * Retorna un arreglo asociativo donde las claves representan los nombres de los campos en un prototipo
     * y los valores representan los nombres correspondientes de los campos en la base de datos.
     *
     * @return array Un arreglo asociativo con la correspondencia entre los nombres de campos en el prototipo y en la base de datos.
     */
    function getDBMap(){
        return $this->map;
    }




    function getBaseSelec(){
        return $this->baseSelect;
    }

    /**
     * Restablece el puntero interno de resultados para permitir obtener datos nuevamente desde el principio.
     */
    function resetGetData(){
        parent::resetPointer($this->sumary);
    }

    /**
     * Obtiene el nuevo ID generado después de una operación de inserción exitosa.
     *
     * @return int|null El nuevo ID generado o null si no hay uno disponible.
     */
    function getNewID(){
        return $this->sumary->new_id;
    }

    /**
     * Registra información relevante en un registro de actividad si la funcionalidad de registro está habilitada.
     *
     * @param array $searchArray Un arreglo con información a ser registrada en el registro de actividad.
     * @throws Exception
     */
    function _recordLog($searchArray){
        if(self::$enableRecordLog){
            $searchArray["desc"] = $this->logDesc;
            $searchArray["tabla"] = parent::getTableName();
            if(isset($_SESSION["USER_ID"])) $searchArray["user_id"] = $_SESSION["USER_ID"];
            $searchArray = parent::putQuoteAndNull($searchArray);

            $sum = parent::_insert(self::$recordTable, $searchArray);

        }
    }

    /**
     * Establece el nombre de la conexión de base de datos que se utilizará para las operaciones.
     *
     * @param string $name El nombre de la conexión de base de datos.
     */
    function setConnectionName($name){
        $this->conectionName = $name;

    }

    /**
     * Obtiene un nuevo ID con un posible prefijo basado en una secuencia, ya sea mediante una función de base de datos o secuencial.
     *
     * @param string|null $sequence El nombre de la secuencia a utilizar. Si es null, se utilizará el nombre de la tabla.
     * @return string|null El nuevo ID generado con el prefijo, o null si no se puede obtener.
     * @throws Exception
     */
    function getPrefixedID($sequence = null): ?string
    {
        if(!$sequence){
            $sequence = $this->tableName;
        }

        if(Environment::$APP_ENABLE_BD_FUNCTION){
            $sql = "SELECT GET_NEXT_ID('$sequence')";

            $newID = $this->execAndFetch($sql);
        }else{
            //busca secuencial
            $sql = "SELECT prefix, size, fill_with, last_id , sufix, eval
				        
						FROM secuential 
						WHERE seq_name = '$sequence' FOR UPDATE";

            $row = $this->execAndFetch($sql);

            //si no existe secuencial lo crea
            if(!$row){
                $sql = "INSERT INTO secuential (seq_name, size, last_id, fill_with,prefix,sufix)
							VALUES ( '$sequence', 8, 0, '','','')";
                $this->execNoQuery($sql);
                $_next_id = 0;
                $row = array(
                    "prefix"=>"",
                    "size"=>"8",
                    "fill_with"=>"",
                    "sufix"=>"",
                    "last_id"=>"0"
                );
            }

            $_next_id = $row["last_id"] + 1;

            //actualiza
            $sql = "UPDATE secuential SET last_id='$_next_id' WHERE seq_name = '$sequence'";
            $this->execNoQuery($sql);

            //retrorna nuevo
            $sql = "SELECT CONCAT(
						ifnull('".$row["prefix"]."',''),
						IFNULL( LPAD('$_next_id', ".$row["size"]." , '".$row["fill_with"]."'), '$_next_id'),
						ifnull( '".$row["sufix"]."','')
										
						) as _result";
            $newID = $this->execAndFetch($sql);
        }

        return $newID;

    }

    /**
     * Verifica si la última ejecución de consulta SQL se realizó sin errores.
     *
     * @return bool True si la última ejecución se realizó sin errores, de lo contrario False.
     */
    function lastExecutionOk(){
        return ($this->sumary->errorNo == 0);
    }

    /**
     * Registra una entrada en el historial si la funcionalidad de historial está habilitada.
     *
     * @param array $searchArray El arreglo de datos a registrar en el historial.
     * @throws Exception
     */
    function _history($searchArray){
        if($this->enableHistory){


            //$searchArray = parent::putQuoteAndNull($searchArray);

            $sum = parent::_insert($this->historyTable, $searchArray, $this->conectionName);

        }
    }

    /**
     * Deshabilita la ejecución automática de la búsqueda de datos.
     */
    function disableExecFind(){
        $this->execFind = false;
    }

    /**
     * Habilita la ejecución automática de la búsqueda de datos.
     */
    function enableExecFind(){
        $this->execFind = true;
    }

    /**
     * Ejecuta la última consulta de búsqueda realizada.
     */
    function findLast(){
        $this->enableExecFind();
        $this->find($this->lastSelectQuery);
    }

    /**
     * Obtiene el número total de filas devueltas por la última consulta ejecutada.
     *
     * @return int El número total de filas.
     */
    function getNumAllRows(){
        return $this->sumary->allRows;
    }

    /**
     * @param $searchArray array asociativo con los campos de la BD
     * @throws Exception
     */
    protected function getFieldsInfo($searchArray): array
    {
        $field_info = array();

        $fields = array_keys($searchArray);
        $fields_all = implode(',', $this->quoteFieldNames($fields));
        $sql = "SELECT " . $fields_all . " FROM " . $this->tableName . " LIMIT 0";
        $sumary = parent::execQuery($sql, true, false, $this->conectionName);

        $i = 0;
        $total = parent::getNumFields($sumary);

        while ($i < $total) {
            $f = $fields[$i];
            $type = parent::getFieldType($sumary, $i);
            $len = parent::getFieldLen($sumary, $i);
            $flag = explode(" ", parent::getFieldFlags($sumary, $i));

            $field_info[$f] = $flag;


            $i++;
        }


        $this->fields_info = $field_info;
        return $field_info;
    }

    /**
     * Comprueba si un campo específico en la tabla de la base de datos es requerido (no nulo) según la definición del esquema.
     *
     * @param string $proto_field_name El nombre del campo en el prototipo.
     * @param array|null $prototype El prototipo del objeto para el cual se verificará el campo requerido.
     * @param bool $force_reload_info Si se establece en true, forzará la recarga de la información de los campos.
     *
     * @return bool Devuelve true si el campo es requerido, de lo contrario devuelve false.
     * @throws Exception
     */
    public function checkFieldRequired($proto_field_name, $prototype = null, $force_reload_info = false): bool
    {
        $status = false;
        $map = $this->getDBMap(); // Obtiene el mapeo de campos de la base de datos

        if($prototype){

            $searchArray = self::mapToBd($prototype, $map, true);

            // Si se debe recargar la información de los campos o no hay información almacenada
            if($force_reload_info || is_null($this->fields_info)){

                // Actualiza la información de los campos
                $this->getFieldsInfo($searchArray);
            }
        }

        // Verifica si el campo buscado está mapeado en la base de datos
        if(isset($map[$proto_field_name])){

            $field_name = $map[$proto_field_name];

            // Si no hay información del campo en la propiedad $fields_info
            if(!isset($this->fields_info[$field_name])){

                $searchArray = parent::mapToBd(array($field_name => null), $map);

                // Obtiene información solo para el campo actual
                $this->getFieldsInfo($searchArray);
            }

            if(in_array("not_null", $this->fields_info[$field_name])){
                $status = true;
            }
        }

        return $status;
    }

    /**
     * Trunca (elimina todos los registros) la tabla asociada a esta instancia de DAO en la base de datos.
     *
     * @return bool Devuelve true si la operación de truncado se realizó correctamente, de lo contrario devuelve false.
     * @throws Exception
     */
    public function truncate(){
        $sql = "TRUNCATE " . $this->tableName;

        return self::execNoQuery($sql);
    }

    /**
     * Obtiene el caché asociado a una clase.
     *
     * @param string $classname El nombre de la clase para la cual se busca el caché.
     * @return CasheFindData|null Retorna una instancia del caché (CasheFindData) si existe, o null si no hay caché disponible.
     */
    protected static function getCache($classname){
        $cashe = null;

        if(isset(self::$cache[$classname])){
            $cashe = self::$cache[$classname];
        }
        return $cashe;
    }
}
