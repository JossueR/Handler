<?php
	loadClass("models/SimpleDAO.php");
	/**
	 * 
	 */
	class AbstractBaseDAO extends SimpleDAO {
		protected $sumary;
		public $autoconfigurable= FALSE;
		public $selectID;
		public $selectName;
		public $errors;
		public $logDesc;
		protected $map;
		protected $prototype;
		protected $baseSelect;
		
		function __construct($tabla, $id, $baseSelect='', $map='', $prototype='') {
			parent::__construct($tabla, $id);
			$this->baseSelect= $baseSelect;
			$this->map= $map;
			$this->prototype = $prototype;
		}
		
		/**
		 * @return QueryInfo
		 */
		function getSumary(){
			return $this->sumary;
		}
		
		function &insert($searchArray){
			$this->sumary = parent::_insert(parent::getTableName(), $searchArray);
			$this->_recordLog(array("Action" => "INSERT"));
			return $this->sumary;
			
		}
		
		function &update($searchArray, $condicion){
			
			$this->sumary = parent::_update(parent::getTableName(), $searchArray, $condicion);
			$this->_recordLog(array("Action" => "UPDATE"));
			return $this->sumary;
			
		}
		
		function &delete($prototype){
			$condicion = parent::mapToBd($prototype, $this->getDBMap());
			$condicion = parent::putQuoteAndNull($condicion);
			
			$this->sumary= parent::_delete(parent::getTableName(), $condicion);
			$this->_recordLog(array("Action" => "DELETE"));
			return $this->sumary;
		
		}
		
		function deleteByID($prototype){
			$searchArray = parent::mapToBd($prototype, $this->getDBMap());
			$condicion = $this->getIdFromDBMap($searchArray);
			$condicion = parent::putQuoteAndNull($condicion);
			$this->sumary = parent::_delete(parent::getTableName(), $condicion);
			
			return $this->sumary->total > 0;
		}
		/***
		 * Busca si existe por ID
		 */
		function exist($searchArray){
			$searchArray = $this->getIdFromDBMap($searchArray);
			
			$sql = "SELECT COUNT(*) FROM " . parent::getTableName() . " WHERE " . parent::getSQLFilter($searchArray);
			return parent::execAndFetch($sql) > 0;
		}
		
		function existBy($searchArray){
			
			$sql = "SELECT COUNT(*) FROM " . parent::getTableName() . " WHERE " . parent::getSQLFilter($searchArray);
			return parent::execAndFetch($sql) > 0;
		}
		
		function getIdFromDBMap($searchArray){
			$condicion = array();
				
			foreach (parent::getId() as $key ) {
				$condicion[parent::getTableName() . "." . $key] = (isset($searchArray[$key]))? $searchArray[$key] : null;
			}
			
			return $condicion;
		}
		
		function getTotals(){
			return $this->sumary->total;
		}
		
		function getFields(){
			$total = mysql_num_fields($this->sumary->result);
			$fields = array();
			for ($i=0; $i < $total; $i++) {
				$fields[] = mysql_field_name($this->sumary->result, $i);
			}
			
			return $fields;
		}
		
		
		/**
		 * Guarda los datos del prototypo
		 * Aplica getDBMap a el prototypo para obtener los nombres de los campos
		 * Si se establee $update, fuerza a generar un update
		 */
		public function save($prototype, $update=2){
			
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
			}
			
			return ($this->sumary->errorNo == 0);
		}
		
		public function find($sql){
			$this->sumary = parent::execQuery($sql, true, $this->autoconfigurable);
		}
		
		
		public function get()
		{
			if($this->sumary->result){
				return parent::getNext($this->sumary);
			}else{
				return false;
			}
		}
		
		public function fetchAll()
		{
			if($this->sumary->result){
				return parent::getAll($this->sumary);
			}else{
				return false;
			}
		}
		
		public function getBy($proto){
			$searchArray = parent::mapToBd($proto, $this->getDBMap());
			
			$searchArray = parent::putQuoteAndNull($searchArray);
			$sql_where = $this->getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec();
			$sql .= " $sql_where";
			
			$this->find($sql);
		}
		
		public function getById($proto){
			$protoDB = parent::mapToBd($proto, $this->getDBMap());
			$searchArray = $this->getIdFromDBMap($protoDB);
			
			$searchArray = parent::putQuoteAndNull($searchArray);
			$sql_where = $this->getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec();
			$sql .= " $sql_where";
			
			$this->find($sql);
		}
		
		public function getFilledPrototype(){
			$prototype = $this->getPrototype();
			$map = $this->getDBMap();
			$map_data = $this->get();
			
			foreach ($prototype as $key => $value) {
				$prototype[$key] = $map_data[$map[$key]];
			}
	
			return $prototype;
		}
		
		public function validate($searchArray){
			$errors = array();
			
			$fields = array_keys($searchArray);
			$fields_all = implode(',', $fields);
			$sql = "SELECT " . $fields_all . " FROM " . $this->tableName . " LIMIT 0";
			$sumary = parent::execQuery($sql, true);

			$i = 0;
			$total = parent::getNumFields($sumary);
			
			while ($i < $total) {
				$f = $fields[$i];
				$type = parent::getFieldType($sumary, $i);
				$len = parent::getFieldLen($sumary, $i);
				$flag = explode(" ", parent::getFieldFlags($sumary, $i));
				
				//verifica requerido
				if(in_array("not_null", $flag)){
					
					if($searchArray[$f] == null || $searchArray[$f] == "null" || $searchArray[$f] == ""){
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
		 * Retorna un arreglo asociativo donde los key , son los distintos campos que se buscaran en el request
		 * y se cargaran automaticamente.
		 * sirve para enmascarar los nombres reales de los campos
		 */
		function getPrototype(){
			return $this->prototype;
		}
		
		/**
		 * Retorna un arreglo asociativo donde los key son los nombres de un prototipo y los value son los nombres de los campos en la base de datos 
		 */
		function getDBMap(){
			return $this->map;
		}
		
		
		
		/**
		 * Es usado para obtener los datos por el id
		 */
		function getBaseSelec(){
			return $this->baseSelect;
		}
		
		function resetGetData(){
			parent::resetPointer($this->sumary);
		}
		
		function getNewID(){
			return $this->sumary->new_id;
		}
	
		function _recordLog($searchArray){
			if(self::$enableRecordLog){
				$searchArray["desc"] = $this->logDesc;
				$searchArray["tabla"] = parent::getTableName();
				if(isset($_SESSION["USER_ID"])) $searchArray["user_id"] = $_SESSION["USER_ID"];
				$searchArray = parent::putQuoteAndNull($searchArray);
				
				$sum = parent::_insert(self::$recordTable, $searchArray);
			
			}
		}
	
	}
	
?>