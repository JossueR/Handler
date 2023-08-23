<?php
namespace HandlerCore\models\dao;

	/**
	 *
	 */
	class ReportDAO extends AbstractBaseDAO {
		const TYPE_REPORTER = "REPORT";
		const TYPE_ACTION = "ACTION";
		const TYPE_LINK = "LINK";

		function __construct() {
			parent::__construct("report", array("id"));
		}

		function getPrototype(){
			$prototype = array(

				'name'=>null,

				'type'=>null,
				'action'=>null,
				'url'=>null,
				'autoconfigurable'=>null,
				'permissions_id'=>null,
				'controls'=>null


			);

			return $prototype;
		}

		function getPrototypeAll(){
			$prototype = array(

				'name'=>null,
				'type'=>null,
				'action'=>null,
				'url'=>null,
				'definition'=>null,
				'format_row'=>null,
				'format_col'=>null,
				'format_totals'=>null,
				'autoconfigurable'=>null,
				'controls'=>null,
				'html_attrs'=>null,
				'permissions_id'=>null,


			);

			return $prototype;
		}

		function getDefinitionPrototype(){
			$prototype = array(

				'definition'=>null
			);

			return $prototype;
		}

		function getRowFormatPrototype(){
			$prototype = array(

				'format_row'=>null
			);

			return $prototype;
		}

		function getColFormatPrototype(){
			$prototype = array(

				'format_col'=>null
			);

			return $prototype;
		}

		function getTotalsFormatPrototype(){
			$prototype = array(

				'format_totals'=>null
			);

			return $prototype;
		}

		function getHtmlAttrsPrototype(){
			$prototype = array(

				'html_attrs'=>null
			);

			return $prototype;
		}


		function getDBMap(){
			$prototype = array(
				'id'=>'id',
				'name'=>'name',
				'definition'=>'definition',
				'type'=>'type',
				'action'=>'action',
				'format_col'=>'format_col',
				'format_row'=>'format_row',
				'format_totals'=>'format_totals',
				'url'=>'url',
				'autoconfigurable'=>'autoconfigurable',
				'controls'=>'controls',
				'html_attrs'=>'html_attrs',
				'permissions_id'=>'permissions_id',
				'active'=>'active'
			);

			return $prototype;
		}

		function getBaseSelec(){
			$sql = "SELECT `report`.`id`,
					    `report`.`name`,
					    `report`.`definition`,
					    `report`.`type`,
					    `report`.`action`,
					    `report`.`url`,
					    `report`.`autoconfigurable`,
					    `report`.`controls`,
					    `report`.`html_attrs`,
					    `report`.`format_col`,
					    `report`.`format_row`,
					    `report`.`format_totals`,
					    `report`.`create_date`,
					    `report`.`create_user`,
					    `report`.`update_date`,
					    `report`.`update_user`,
					    `report`.`permissions_id`,
					    `report`.`active`
					FROM `report`
					WHERE ";

			return $sql;
		}


		function getActives(){
			$searchArray["report.active"] = self::REG_ACTIVO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}





		function getInactives(){
			$searchArray["report.active"] = self::REG_DESACTIVADO_TX;
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
