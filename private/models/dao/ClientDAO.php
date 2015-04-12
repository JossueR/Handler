<?php
	loadClass("models/dao/AbstractBaseDAO.php");
	/**
	 * 
	 */
	class ClientDAO extends AbstractBaseDAO {
		
		function __construct() {
			parent::__construct("client", array("id"));
		}
		
		function getPrototype(){
			$prototype = array(

				'name'=>null,
				'lastname'=>null,
				'DNI'=>null,
				'sex'=>null,
				'city'=>null,
				'adress'=>null,
				'birthday'=>null,
				'is_passient'=>null,
				'is_emp'=>null
			);
			
			return $prototype;
		}

		
		function getDBMap(){
			$prototype = array(
				'id'=>'id',
				'name'=>'name',
				'active' =>'active',
				'name'=>'name',
				'lastname'=>'lastname',
				'DNI'=>'DNI',
				'sex'=>'sex',
				'city'=>'city',
				'adress'=>'adress',
				'birthday'=>'birthday',
				'is_passient'=>'is_passient',
				'is_emp'=>'is_emp',
				'img_url'=>'img_url'
			);
			
			return $prototype;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `client`.`id`,
					    `client`.`DNI`,
					    `client`.`name`,
					    `client`.`create_date`,
					    `client`.`create_user`,
					    `client`.`update_date`,
					    `client`.`update_user`,
					    `client`.`active`,
					    `client`.`is_passient`,
					    `client`.`is_emp`,
					    `client`.`uid`,
					    `client`.`lastname`,
					    `client`.`adress`,
					    `client`.`city`,
					    `client`.`country`,
					    `client`.`img_url`,
					    `client`.`birthday`,
					    `client`.`sex`,
					    `client`.`img_url`
					FROM client
					WHERE ";
					
			return $sql;
		}
		
		
		function getActives(){
			$searchArray["client.active"] = self::REG_ACTIVO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		

		
		function getInactives(){
			$searchArray["client.active"] = self::REG_DESACTIVADO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		
		function getPassientsActives(){
			$searchArray["client.active"] = self::REG_ACTIVO_TX;
			$searchArray["client.is_passient"] = "Y";
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		

		
		function getPassientsInactives(){
			$searchArray["client.active"] = self::REG_DESACTIVADO_TX;
			$searchArray["client.is_passient"] = "Y";
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		
		function getEmpsActives(){
			$searchArray["client.active"] = self::REG_ACTIVO_TX;
			$searchArray["client.is_emp"] = "Y";
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		

		
		function getEmpsInactives(){
			$searchArray["client.active"] = self::REG_DESACTIVADO_TX;
			$searchArray["client.is_emp"] = "Y";
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}

		
		function &insert($searchArray){
			$defaul["create_date"] = self::$SQL_TAG."now()";
			$defaul["create_user"] = $_SESSION['USER_NAME'];
			$defaul["active"] = self::REG_ACTIVO_TX;
			$defaul = parent::putQuoteAndNull($defaul);
			
			$searchArray = array_merge($searchArray, $defaul);
			

			return parent::insert($searchArray);
			
		}
		
		function &update($searchArray, $condicion){
			$defaul["update_date"] = self::$SQL_TAG."now()";
			$defaul["update_user"] = $_SESSION['USER_NAME'];
			$defaul = parent::putQuoteAndNull($defaul);
			
			$searchArray = array_merge($searchArray, $defaul);
			return parent::update($searchArray, $condicion);
		}

	}
	
?>