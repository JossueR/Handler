<?php
	loadClass("models/dao/AbstractBaseDAO.php");
	/**
	 * 
	 */
	class ConfigVarDAO extends AbstractBaseDAO {
		
		function __construct() {
			parent::__construct("config_vars", array("var"));
		}
		
		function getPrototype(){
			$prototype = array(

				'name'=>null
			);
			
			return $prototype;
		}

		
		function getDBMap(){
			$prototype = array(
				'var'=>'var',
				'val'=>'val'
			);
			
			return $prototype;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `config_vars`.`var`,
					    `config_vars`.`val`
					    
					FROM `config_vars`
					WHERE ";
					
			return $sql;
		}
		
		
		function getActives(){
			$searchArray = array();
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		
		public function getVar($var)
		{
			
			$this->getById(array("var"=>$var));
			$row = $this->get();
			
			return (isset($row["var"]))?  $row["val"]: null;
		}
		
		public function setVar($var,$val)
		{
			$prototype = array();
			$prototype["var"]= $var;
			$prototype["val"]= $val;
			
			return $this->save($prototype);
		}
		
	}
	
?>