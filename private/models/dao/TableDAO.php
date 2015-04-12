<?php
	loadClass("models/dao/AbstractBaseDAO.php");
	/**
	 * 
	 */
	class TableDAO extends AbstractBaseDAO {
		const TYPE_SINGLE = "SINGLE";
		const TYPE_GROUP = "GROUP";
		const TYPE_MASTER = "MASTER";
		
		function __construct() {
			parent::__construct("tables", array("id"));
		}
		
		function getPrototype(){
			$prototype = array(

				'name'=>null,
				'seat'=>null
			);
			
			return $prototype;
		}

		
		function getDBMap(){
			$prototype = array(
				'id'=>'id',
				'name'=>'name',
				'active'=>'active',
				'type'=>'type',
				'last_order_id'=>'last_order_id',
				'seat' =>'seat'
			);
			
			return $prototype;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `tables`.`id`,
					    `tables`.`name`,
					    `tables`.`active`,
					    `tables`.`seat`,
					    `tables`.`type`,
					    `tables`.`last_order_id`,
					    `tables`.`createDate`,
					    `tables`.`createUser`,
					    `tables`.`updateDate`,
					    `tables`.`updateUser`
					FROM `tables`
					WHERE ";
					
			return $sql;
		}
		
		
		function getActives(){
			$searchArray["tables.active"] = self::REG_ACTIVO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		

		
		function getInactives(){
			$searchArray["tables.active"] = self::REG_DESACTIVADO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}

		
		function &insert($searchArray){
			$defaul["createDate"] = self::$SQL_TAG."now()";
			$defaul["createUser"] = $_SESSION['USER_NAME'];
			$defaul["active"] = self::REG_ACTIVO_TX;
			$defaul = parent::putQuoteAndNull($defaul);
			
			$searchArray = array_merge($searchArray, $defaul);
			

			return parent::insert($searchArray);
			
		}
		
		function &update($searchArray, $condicion){
			$defaul["updateDate"] = self::$SQL_TAG."now()";
			$defaul["updateUser"] = $_SESSION['USER_NAME'];
			$defaul = parent::putQuoteAndNull($defaul);
			
			$searchArray = array_merge($searchArray, $defaul);
			return parent::update($searchArray, $condicion);
		}
		
		function validateName($name){
			$searchArray["tables.active"] = self::REG_ACTIVO_TX;
			$searchArray["tables.name"] = $name;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			return $this->existBy($searchArray);

		}
		
		function getAsocToGroup($group_id){

			$sql = "SELECT `tables`.`id`,
					    `tables`.`name`,
					    tables.seat,
					    tables.type
					FROM `tables`
					JOIN group_table gt on gt.table_id=`tables`.`id`
					WHERE gt.group_id=$group_id
					GROUP BY `tables`.`id`";
			
				
			$this->find($sql);
		}
		
		function getNotAsocToGroup($group_id){

			$sql = "SELECT `tables`.`id`,
					    `tables`.`name`,
					    tables.seat,
					    tables.type
					FROM `tables`
					WHERE `tables`.`id` not in (
						select  gt.table_id from group_table gt WHERE gt.group_id=$group_id
					)
					AND tables.id <> $group_id
					AND tables.active='".self::REG_ACTIVO_TX."'";
			
				
			$this->find($sql);
		}
		
		function addToGroup($group_id, $table_id){
			
			$searchArray["group_id"] = $group_id;
			$searchArray["table_id"] = $table_id;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			$sumary = self::_insert("group_table", $searchArray);
			
			#actualiza mesa secundaria
			$searchArray = array();
			$searchArray["id"] = $table_id;
			$searchArray["type"] = self::TYPE_GROUP;
			
			$this->save($searchArray);
			
			#ctualiza mesa principal
			$searchArray = array();
			$searchArray["id"] = $group_id;
			$searchArray["type"] = self::TYPE_MASTER;
			
			$this->save($searchArray);
			
			return ($sumary->errorNo == 0);
		}
		
		function delToGroup($group_id, $table_id){
			
			$searchArray["group_id"] = $group_id;
			$searchArray["table_id"] = $table_id;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			$sumary = self::_delete("group_table", $searchArray);
			
			#actualiza mesa secundaria
			$searchArray = array();
			$searchArray["id"] = $table_id;
			$searchArray["type"] = self::TYPE_SINGLE;
			
			$this->save($searchArray);
			
			$this->unsetMaster($group_id);
			
			return ($sumary->errorNo == 0);
		}
		
		function unsetMaster($group_id){
			$sql ="SELECT count(*) as cant FROM group_table where group_id = $group_id";
			$this->find($sql);
			$data = $this->get();
			
			if($data["cant"] == 0){
				#ctualiza mesa principal
				$searchArray = array();
				$searchArray["id"] = $group_id;
				$searchArray["type"] = self::TYPE_SINGLE;
				
				$this->save($searchArray);
			}
		}
		
		function setLastOrderID($table_id, $order_id){
			
				#ctualiza mesa principal
				$searchArray = array();
				$searchArray["id"] = $table_id;
				$searchArray["last_order_id"] = $order_id;
				
				$this->save($searchArray);
			
		}

	}
	
?>