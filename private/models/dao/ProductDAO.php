<?php
	loadClass("models/dao/AbstractBaseDAO.php");
	/**
	 * 
	 */
	class ProductDAO extends AbstractBaseDAO {
		
		function __construct() {
			parent::__construct("product", array("product_id"));
		}
		
		function getPrototype(){
			$prototype = array(

				'name'=>null,
				'description'=>null,
				
				'upc'=>null,
				
				'published'=>null,
				'price'=>null
				
			);
			
			return $prototype;
		}

		
		function getDBMap(){
			$prototype = array(
				'product_id'=>'product_id',
				'name'=>'name',
				'description'=>'description',
				
				'upc'=>'upc',
				
				'published'=>'published',
				
				'price'=>'price',
				
				'default_img'=>'default_img',
				'active' =>'active'
			);
			
			return $prototype;
		}
		
		function getSelecList(){
			$sql = "SELECT `product`.`product_id`,
					    `product`.`name`,
					    `product`.`description`,
					   
					    `product`.`upc`,
					    
					    `product`.`price`,
					    
					    `product`.`active`,
					    `product`.`published`,
					    `product`.`default_img`,
					    i.url,
					    `product`.`createDate`,
					    `product`.`createUser`,
					    `product`.`updateDate`,
					    `product`.`updateUser`,
					    GROUP_CONCAT(c.name) as categorias
					FROM `product`
					LEFT JOIN product_has_category phc on phc.product_id = `product`.`product_id`
                    LEFT JOIN category c on c.category_id=phc.category_id
                    LEFT JOIN img i on i.img_id=default_img
					WHERE 
					GROUP BY `product`.`product_id`";
					
			return $sql;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `product`.`product_id`,
					    `product`.`name`,
					    `product`.`description`,
					   
					    `product`.`upc`,
					    
					    `product`.`price`,
					    
					    `product`.`active`,
					    `product`.`published`,
					    
					    `product`.`createDate`,
					    `product`.`createUser`,
					    `product`.`updateDate`,
					    `product`.`updateUser`
					FROM `product`
					
					WHERE 
					";
					
			return $sql;
		}
		
		
		function getActives(){
			$searchArray["product.active"] = self::REG_ACTIVO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = str_replace("WHERE", "WHERE " . $where, $this->getBaseSelec());
			
				
			$this->find($sql);
		}
		

		
		function getInactives(){
			$searchArray["product.active"] = self::REG_DESACTIVADO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = str_replace("WHERE", "WHERE " . $where, $this->getBaseSelec());
			
				
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
		
		function getRoots(){
			$searchArray["phc.category_id"] = self::$SQL_TAG . " IS NULL";
			$searchArray["product.active"] = self::REG_ACTIVO_TX;
			
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			
			$sql = str_replace("WHERE", "WHERE " . $where, $this->getSelecList());
				
			$this->find($sql);
		}
		
		function getByCategory($category_id){
			$searchArray["phc.category_id"] = $category_id;
			$searchArray["product.active"] = self::REG_ACTIVO_TX;
			
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			
			$sql = str_replace("WHERE", "WHERE " . $where, $this->getSelecList());
				
			$this->find($sql);
		}

	}
	
?>