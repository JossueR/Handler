<?php
namespace HandlerCore\models\dao;

	/**
	 *
	 */
	class ReportFilterDAO extends AbstractBaseDAO {

		function __construct() {
			parent::__construct("report_filter", array("id"));
		}

		function getPrototype(){
			$prototype = array(

				'type'=>null,
				'join'=>null,
				'order'=>null,
				'field'=>null,
				'op'=>null,
				'value'=>null,
				'root'=>null,
				'child'=>null,
				'sibling'=>null


			);

			return $prototype;
		}


		function getDBMap(){
			$prototype = array(
				'id'=>'id',
				'subreport_id'=>'subreport_id',
				'report_id'=>'report_id',
				'active'=>'active',
				'type'=>'type',
				'join'=>'join',
				'order'=>'order',
				'field'=>'field',
				'label'=>'label',
				'op'=>'op',
				'value'=>'value',
				'root'=>'root',
				'child'=>'child',
				'sibling'=>'sibling'
			);

			return $prototype;
		}

		function getBaseSelec(){
			$sql = "SELECT `report_filter`.`id`,
					    `report_filter`.`subreport_id`,
					    `report_filter`.`report_id`,
					    `report_filter`.`create_date`,
					    `report_filter`.`create_user`,
					    `report_filter`.`update_date`,
					    `report_filter`.`update_user`,
					    `report_filter`.`active`,
					    `report_filter`.`type`,
					    `report_filter`.`join`,
					    `report_filter`.`order`,
					    `report_filter`.`field`,
					    `report_filter`.`label`,
					    `report_filter`.`op`,
					    `report_filter`.`value`,
					    `report_filter`.`root`,
					    `report_filter`.`child`,
					    `report_filter`.`sibling`
					FROM `report_filter`
					WHERE ";

			return $sql;
		}


		function getByReport($report_id){
			$searchArray["report_filter.report_id"] = $report_id;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}

		function getBySubReport($report_id){
			$searchArray["report_filter.subreport_id"] = $report_id;
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

