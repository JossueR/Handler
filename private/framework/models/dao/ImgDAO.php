<?php
	loadClass("models/dao/AbstractBaseDAO.php");
	/**
	 * 
	 */
	class ImgDAO extends AbstractBaseDAO {
		
		function __construct() {
			parent::__construct("img", array("img_id"));
		}
		
		function getPrototype(){
			$prototype = array(

				'description'=>null,
				'product_id'=>null,
				'reff_id'=>null,
				'reff_type'=>null,
				'type'=>null
			);
			
			return $prototype;
		}

		
		function getDBMap(){
			$prototype = array(
				'img_id'=>'img_id',
				'url'=>'url',
				'content_type'=>'content_type',
				'reff_id'=>'refference_id',
				'reff_type'=>'refference_type',
				'type'=>'type',
				'description'=>'description'
			);
			
			return $prototype;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `img`.`img_id`,
					    `img`.`description`,
					    `img`.`url`,
					    `img`.`content_type`,
					    `img`.`refference_type`,
					    `img`.`refference_id`,
					    `img`.`type`,
					    `img`.`createDate`,
					    `img`.`createUser`,
					    `img`.`updateDate`,
					    `img`.`updateUser`
					FROM `img`
					WHERE ";
					
			return $sql;
		}
		
		
		function getActives($reff_type, $reff_id, $type = null){
			$searchArray["img.refference_type"] = $reff_type;
			$searchArray["img.refference_id"] = $reff_id;
			
			if($type){
				$searchArray["img.type"] = $type;
			}
			
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}

		
		function &insert($searchArray){
			$defaul["createDate"] = self::$SQL_TAG."now()";
			$defaul["createUser"] = $_SESSION['USER_NAME'];

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