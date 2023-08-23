<?php
namespace HandlerCore\models\dao;
	/**
	 *
	 */
	class BookmarkDAO extends AbstractBaseDAO {

		function __construct() {
			parent::__construct("bookmark", array("invoker","create_user"));
		}

		function getPrototype(){
			$prototype = array(
				'invoker'=>null,
				'create_user'=>null,
				'page'=>null,
				'order_field'=>null,
				'order_type'=>null
			);

			return $prototype;
		}


		function getDBMap(){
			$prototype = array(
				'invoker' => 'invoker',
				'page' => 'page',
				'search' => 'search',
				'order_field' => 'order_field',
				'order_type' => 'order_type',
				'create_user' => 'create_user',
				'active' => 'active'
			);

			return $prototype;
		}

		function getBaseSelec(){
			$sql = "SELECT 
					    `bookmark`.`invoker`,
					    `bookmark`.`page`,
					    `bookmark`.`search`,
					    `bookmark`.`order_field`,
					    `bookmark`.`order_type`,
					    `bookmark`.`create_date`,
					    `bookmark`.`create_user`,
					    `bookmark`.`update_date`,
					    `bookmark`.`update_user`,
					    `bookmark`.`active`
					FROM `bookmark`
					WHERE ";

			return $sql;
		}


		function getActives(){
			$searchArray["bookmark.active"] = self::REG_ACTIVO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}

		function getMyBookmark($invoker){
			$searchArray["bookmark.create_user"] = $_SESSION['USER_NAME'];
			$searchArray["bookmark.invoker"] = $invoker;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}





		function getInactives(){
			$searchArray["bookmark.active"] = self::REG_DESACTIVADO_TX;
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
