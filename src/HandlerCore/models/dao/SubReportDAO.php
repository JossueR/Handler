<?php
namespace HandlerCore\models\dao;

	/**
	 *
	 */
	class SubReportDAO extends AbstractBaseDAO {

		function __construct() {
			parent::__construct("subreport", array("id"));
		}

		function getPrototype(){
			$prototype = array(

				'name'=>null,
				'definition'=>null


			);

			return $prototype;
		}


		function getDBMap(){
			$prototype = array(
				'id'=>'id',
				'name'=>'name',
				'active'=>'active'
			);

			return $prototype;
		}

		function getBaseSelec(){
			$sql = "SELECT `subreport`.`id`,
					    `subreport`.`name`,
					    `subreport`.`create_date`,
					    `subreport`.`create_user`,
					    `subreport`.`update_date`,
					    `subreport`.`update_user`,
					    `subreport`.`active`
					FROM `subreport`
					WHERE ";

			return $sql;
		}


		function getActives(){
			$searchArray["subreport.active"] = self::REG_ACTIVO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}





		function getInactives(){
			$searchArray["subreport.active"] = self::REG_DESACTIVADO_TX;
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
