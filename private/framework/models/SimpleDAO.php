<?php 
/**
*Create Date: 07/22/2011 01:00:56
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 234 $
*/

loadClass("models/QueryInfo.php");

class SimpleDAO{
	static private $conectado=false;
	const REG_ACTIVO='1';
	const REG_DESACTIVADO='0';
	const REG_ACTIVO_TX='ACTIVE';
	const REG_DESACTIVADO_TX='INACTIVE';
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
	
	private static $conections = array();
	
	public static $defaultConection = null;
	
	public static $enableRecordLog = false;
	
	public static $recordTable = "record";
	
	protected static $escapeHTML = true;
	
	function __construct($tableName, $id){
		$this->tableName=$tableName;
		
		$this->TablaId=$id;
	}
	
	function getTableName(){
		return $this->tableName;
	}
	
	function getId(){
		return $this->TablaId;
	}
	
	/**
	 * 
	 * Conecta a una base de datos mysql y establese el charset a utf8
	 * @param $host
	 * @param $bd
	 * @param $usuario
	 * @param $pass
	 * @param $conectionName nombre de referencia de la coneccion
	 */
	static function connect($host,$bd,$usuario,$pass, $conectionName='db') {
		self::$conectado = false;
		
		$coneccion=mysql_connect($host,$usuario,$pass);
		
		if($coneccion){
			$bdConexion=mysql_select_db($bd,$coneccion);
			
			if($bdConexion){
				mysql_query("SET NAMES 'utf8'",$coneccion);
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
	 * Escapa una cadena para ser enviada a la base de datos
	 * @param $str
	 */
	static public function escape($str){
		return mysql_real_escape_string($str);
	}
	
	/**
	 * Obtiene el query del archivo $sqlFile.
	 * agrega PATH_PRIVATE a $sqlFile
	 */
	static private function getQuery($sqlFile){
		$query = "";
		
		//si existe el archivo
		if(file_exists(PATH_PRIVATE . $sqlFile)){
			
			$query = file_get_contents(PATH_PRIVATE . $sqlFile);
		}else{
			//echo "no accesible " . PATH_PRIVATE . $sqlFile;
		}
		
		return $query;
	}
	

	/**
	 * 	
	 * @param $sql es el query.
	 * @param $isSelect: boleano, default es true. Indica si se ejecuta un select.
	 * Si se ejecuta un select , carga $array['total']
	  * @param $isAutoConfigurable: boleano, default es false. Indica si Agrega limit order y campos adicionales de filtrado u paginacion
	 * @return QueryInfo
	 */
	static public function &execQuery($sql,$isSelect= true, $isAutoConfigurable= false, $conectionName=null){
		$sumary = new QueryInfo();
		
		if(!$conectionName || !isset(self::$conections[$conectionName])){
			$conectionName = self::$defaultConection;
		}
		
		//agrega paginacion
		if($isAutoConfigurable){
			$sql = self::addGroups($sql);
			$sql = self::addFilters($sql);
			$sql = self::addOrder($sql);
			$sql = self::addPagination($sql);
		}
		
		//muestra el sql si se habilita el modo depuracion
		if($_SESSION['SQL_SHOW']){
			echo $sql . "<br />\n";
		}
		
		
		$sumary->result = mysql_query($sql, self::$conections[$conectionName]->connection);

		
		if($isSelect){
			
			$sumary->total  = ($sumary->result)? intval(mysql_num_rows($sumary->result)) : 0;
		}else{
			$sumary->total = mysql_affected_rows();
			$sumary->new_id = mysql_insert_id();
		}
		
		$sumary->errorNo = mysql_errno();
		
		$sumary->error = mysql_error();
		
		// si hay paginacion
		if($isAutoConfigurable){
			$sql = "SELECT FOUND_ROWS();";
			$rows = mysql_query($sql, self::$conections[$conectionName]->connection);
			$rows = mysql_fetch_row($rows);
			
			
			$sumary->allRows = $rows[0];
		}else{
			$sumary->allRows = $sumary->total;
		}
		
		echo $sumary->error;
		 
		return $sumary;
		#return new QueryInfo();
	}

	/**
	 * 
	 * Arma querys tipo select
	 * Reemplaza los tokens CON EL FORMATO {nombre_token} por el valor en el array respectivo
	 * 
	 */ 
	static private function builtQuery($sql, $array){
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
	

	static public function execQueryFile($sqlFile, $inputArray, $conectionName= null){
		if(!$conectionName || !isset(self::$conections[$conectionName])){
			$conectionName = self::$defaultConection;
		}
		
		$sql = self::builtQuery(self::getQuery($sqlFile), $inputArray);
		return self::execQuery($sql,TRUE,FALSE,self::$conections[$conectionName]->connection);
	}
	
	static public function execAndFetch($sql, $conectionName= null){
		if(!$conectionName || !isset(self::$conections[$conectionName])){
			$conectionName = self::$defaultConection;
		}
		
		//muestra el sql si se habilita el modo depuracion
		if($_SESSION['SQL_SHOW']){
			echo $sql . "<br />\n";
		}
		$rows = mysql_query($sql, self::$conections[$conectionName]->connection);
		
		
		if(mysql_errno() == 0){
				
			if(mysql_num_fields($rows) == 1){
				$rows = mysql_fetch_row($rows);
				return $rows[0];
			}else{
				$rows = mysql_fetch_array($rows, MYSQL_ASSOC);
				return $rows;
			}
		}else{
			return null;
		}
	}
	
	static public function getNext(QueryInfo &$sumary){
		
		if(!isset($sumary->total) || $sumary->total == 0){
			return null;
		}else if(self::$inArray){

		 	if(self::$inAssoc){
		 		$type=MYSQL_ASSOC;
			}else{
				$type=MYSQL_NUM;
			}
			
			return self::escape_HTML(mysql_fetch_array($sumary->result, $type));
		}else{
			return self::escape_HTML(mysql_fetch_row($sumary->result));
		}
	}
	
	static public function getAll(QueryInfo &$sumary){
		$valores = array();
		
		while($row = self::getNext($sumary)){
			$valores[] = $row;
		}
		
		return $valores;
	}
	
	/**
	 * Retorna un arreglo sin las posicieones vacias
	 */
	static public function cleanEmptys($searchArray)
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
	 * 
	 * Agrega comilla a todos los elementos del arraglo que sean string 
	 * @param <SQL> indica que es fragmento sql 
	 * @param unknown_type $array
	 */
	static public function putQuoteAndNull($array, $removeTag = self::REMOVE_TAG ){
		//si hay registros
		if(count($array)>0){
			
			//para cada elemento
			foreach ($array as $key => $value) {
				
				//si el valor no es un array
				if(!is_array($value)){
					
					//Pone valor null si el elemento es nulo o vacio
					if(is_null($value) || strlen($value) == 0){
						$array[$key] = "null";
					}else{
						
						//Si el elemento contiene el tag <SQL>
						if(substr_count($value, self::$SQL_TAG) > 0){
							
							//Elimina el tag si se configura
							if($removeTag){
								//Elimina el tag <SQL>
								$value = str_replace(self::$SQL_TAG, "", $value);
							}
							
							
							//no realiza ninguna conversion
							$array[$key] =  $value;
							
						//Elemente no tiene tag <SQL>
						}else{
							
							//Agrega comillas
							$array[$key] = "'" . self::escape($value) . "'";
						}
							
						
						
					}
				}else{
					$value = self::putQuoteAndNull($value);
					
					//Asocia nuevamente los datos escapados
					$array[$key] =  $value;
				}
			}
		}
		
		//retorna los datos trabajados
		return $array;
	}
	
	/**
	 * 
	 * Genera fragmento sql con filtros a partir del arreglo $filterArray
	 * @param $filterArray
	 * @return string sql con los filtos
	 */
	static public function getSQLFilter($filterArray, $join = self::AND_JOIN){
		
		//pone datos nulos y comillas
		//$searchArray = self::putQuoteAndNull($filterArray,self::NO_REMOVE_TAG);
		$searchArray = $filterArray;
		
		//inicializa el campo q sera devuelto
		$campos = array();
		
		//Si el arreglo de filtros no esta vacio
		if(count($filterArray)>0){
				
			//para cara elemento, ya escapado
			foreach ($searchArray as $key => $value) {
				
				//Si el elemento no es nulo
				if($value != null){
					
					//si es un arreglo genera un IN
					if(is_array($value)){
						
						//Une los valores del array y los separa por comas
						$value = implode(" ,", $value);
						
						//almacena el filtro IN
						$campos[] = "$key IN(". $value . ") ";
						
					//Si no es un arreglo
					}else{
						
						//Si el elemento contiene el tag <SQL>
						if(substr_count($value, self::$SQL_TAG) > 0){
							
							
							//Elimina el tag <SQL>
							$value = str_replace(self::$SQL_TAG, "", $value);
							
							$campos[] = "$key $value";
							
						//Elemente no tiene tag <SQL>
						}else{
							$campos[] = "$key=".$value;
						}
					}
				}
			}
			
		}
		$campos = implode($campos, $join);
		return " (" . $campos . ") ";
	}
	
	static public function StartTransaction(){
		$sql = "START TRANSACTION";
		self::execQuery($sql, false);
	}
	
	static public function CommitTransaction(){
		$sql = "COMMIT";
		self::execQuery($sql, false);
	}
	
	static public function RollBackTransaction(){
		$sql = "ROLLBACK";
		self::execQuery($sql, false);
	}
	
	/***
	 * Genera un insert de la tabla con los datos de el searcharray
	 */
	static public function &_insert($table, $searchArray){
		
		//Obtiene nombre de los campos
		$def=array_keys($searchArray);
		
		//Para cada campo
		for ($i=0; $i < count($def); $i++) {
			
			//Agrega comillas 
			$def[$i] = "`" . $def[$i] . "`";
		}
		
		//genera insert
		$sql = "INSERT INTO $table(". implode(",", $def) . ") VALUES(" . implode(",", $searchArray) . ")";
		
		//ejecuta
		return self::execQuery($sql, false);
	}
	
	/***
	 * Genera un update de la tabla con los datos de el searcharray
	 */
	static public function &_update($table, $searchArray, $condicion){
		$def=array_keys($searchArray);
		
		$sql = "UPDATE $table SET ";
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
		return self::execQuery($sql, false);
	}
	
	/***
	 * Genera un update de la tabla con los datos de el searcharray
	 */
	static public function &_delete($table, $condicion){
		
		$sql = "DELETE FROM $table ";

		
		$sql .= " WHERE ";

		$sql .=  self::getSQLFilter($condicion);
			
		//echo $sql;
		return self::execQuery($sql, false);
	}
	
	/**
	 * Retorna un arreglo con los nombres de los campos de la BD
	 * @prototype es un arreglo con los nombre de los campos de un formulario
	 * @map Arreglo que contiene la equivalencia de [Nombre_Campo_del_Formulario]=campo_BD
	 */
	static public function mapToBd($prototype, $map){
		$searchArray = array();
		foreach ($map as $key => $value) {
			if(isset($prototype[$key])){
				$searchArray[$value] = $prototype[$key];
			}
		}
		return $searchArray;
	}
	
	/**
	 * 
	 */
	static protected function addPagination($sql){
		$page = intval( Handler::getRequestAttr("PAGE") );
		
		//agrega limit si page es un numero mayor a cero
		if($page >= 0){
			//agrega SQL_CALC_FOUND_ROWS al query
			$sql = trim($sql);
			$sql = str_replace("\n", " ", $sql);
			$exploded = explode(" ", $sql);
			$exploded[0] .= " SQL_CALC_FOUND_ROWS ";
			$sql = implode(" ", $exploded);

		
			$desde = ($page) * APP_DEFAULT_LIMIT_PER_PAGE;
			$sql .= " LIMIT $desde, " . APP_DEFAULT_LIMIT_PER_PAGE;
		}
		return $sql;
	}
	
	static protected function addOrder($sql){
		$field = Handler::getRequestAttr("FIELD");
		$asc = Handler::getRequestAttr("ASC");

		
		//agrega SQL_CALC_FOUND_ROWS al query
		$sql = trim($sql);
		
		if(!is_null($field)){
		
			// solo acepta A o D
			$asc = ( $asc == 'D')? 'DESC':'ASC';
			
			$sql = $sql . " ORDER BY $field $asc ";
		}
		
		return $sql;
	}
	
	
	static protected function addFilters($sql){
		$filters = Handler::getRequestAttr("FILTER");
		$columns = Handler::getRequestAttr("FILTER_KEYS");
		
		if($filters){
			
			$filters = explode(" ", $filters);
			$columns = explode(",", $columns);
			
			
			
			$sql_filter = array();
			//echo var_dump($columns);
			//echo count($columns);
			for($x=0; $x < count($columns); $x++){
				//echo $x . " ";
				if(empty($columns[$x]) || !strpos($sql, $columns[$x])){
					continue;
				}
				
				$sql_filter[] = "`" . $columns[$x] . "` LIKE '%%'";
			}
			$sql_filter = implode(" OR ", $sql_filter);
			$sql_filter = "($sql_filter)";
			
			$all_filters = array();
			foreach ($filters as $text) {
				$advance = explode("::", $text);
				
				//si son tres trextos separados por dos puntos y el primer texto esta en el query
				if(count($advance) == 3 && strpos($sql, $advance[0]) ){
					
					$advance[2] = str_replace(';;', ' ', $advance[2]);
					
					$val_org = $advance[2];
					 
					if(validDate($advance[2])){
						$advance[0] = "STR_TO_DATE($advance[0],'".DB_DISPLAY_DATE_FORMAT."')";
						$advance[2] = "STR_TO_DATE('{$advance[2]}','".DB_DISPLAY_DATE_FORMAT."')";
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
					}
				}else{
					$all_filters[] = str_replace("%%", "%$text%", $sql_filter);
				}
			}
			$all_filters = implode(" AND ", $all_filters);
			
			
			$sql .= " HAVING $all_filters ";
		}
		
		return $sql;
	}
	
	static protected function addGroups($sql){
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
	
	static protected function getConfigs($orderField, $asc=true, $page=-1, $limitPerPage=0, $groupDefault=null){
		//injecta en el post valores para agregar paginacion  y ordenado
		
		if(!isset($_POST['PAGE'])){
			$_POST['FIELD'] = $orderField;
			$_POST['ASC'] = $asc;
			$_POST['PAGE'] = $page;
		}
		
		if(!isset($_POST['GROUPS'])){
			$_POST['GROUPS'] = $groupDefault;
		}
		
	}
	
	static public function getDateTimeFormat($field){
		$str = " DATE_FORMAT($field,'%d-%m-%Y %h:%i:%s %p') ";
		return $str;
	}
	
	static public function getDateFormat($field){
		$str = " DATE_FORMAT($field,'%d-%m-%Y') ";
		return $str;
	}
	
	static public function getTimeFormat($field){
		$str = " DATE_FORMAT($field,'%h:%i:%s %p') ";
		return $str;
	}
	
	static public function getHourFormat($field){
		$str = " DATE_FORMAT($field,'%H') ";
		return $str;
	}
	
	static public function getNumFields(QueryInfo &$sumary){
		return mysql_num_fields($sumary->result);
	}
	
	static public function getFieldInfo(QueryInfo &$sumary, $i){
		return mysql_fetch_field($sumary->result, $i);
	}
	
	static public function getFieldType(QueryInfo &$sumary, $i){
		return mysql_field_type($sumary->result, $i);
	}
	
	static public function getFieldLen(QueryInfo &$sumary, $i){
		return mysql_field_len($sumary->result, $i);
	}
	
	static public function getFieldFlags(QueryInfo &$sumary, $i){
		return mysql_field_flags($sumary->result, $i);
	}
	
	static public function escaoeHTML_ON(){
		self::$escapeHTML=true;
	}
	
	static public function escaoeHTML_OFF(){
		self::$escapeHTML=false;
	}
	
	static public function escape_HTML($data){
		
		if(self::$escapeHTML && is_array($data)){
			foreach ($data as $key => $value) {
				$data[$key] = htmlspecialchars($value, ENT_QUOTES , "UTF-8");
			}
		}
		
		return $data;
	}
	
	function resetPointer(QueryInfo &$sumary, $pos = 0){
		return mysql_data_seek( $sumary->result , $pos);
	}
}
?>