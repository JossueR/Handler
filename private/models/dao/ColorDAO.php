<?php
	loadClass("models/dao/AbstractBaseDAO.php");
	/**
	 * 
	 */
	class ColorDAO extends AbstractBaseDAO {
		
		function __construct() {
			parent::__construct("color", array("color_id"));
		}
		
		function getPrototype(){
			$prototype = array(

				'name'=>null
			);
			
			return $prototype;
		}

		
		function getDBMap(){
			$prototype = array(
				'color_id'=>'color_id',
				'name'=>'name',
				'active' =>'active'
			);
			
			return $prototype;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `color`.`color_id`,
					    `color`.`name`,
					    `color`.`active`,
					    `color`.`createDate`,
					    `color`.`createUser`,
					    `color`.`updateDate`,
					    `color`.`updateUser`
					FROM `color`
					WHERE ";
					
			return $sql;
		}
		
		
		function getActives(){
			$searchArray["color.active"] = self::REG_ACTIVO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		

		
		function getInactives(){
			$searchArray["color.active"] = self::REG_DESACTIVADO_TX;
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

	}
	
?>