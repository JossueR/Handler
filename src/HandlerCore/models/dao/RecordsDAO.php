<?php
namespace HandlerCore\models\dao;
	/**
	 *
	 */
	class RecordsDAO extends AbstractBaseDAO {

		function __construct() {
			parent::__construct("records", array("id"));
		}

		function getPrototype(){
			$prototype = array(


			);

			return $prototype;
		}


		function getDBMap(){
			$prototype = array(

			);

			return $prototype;
		}

		function getBaseSelec(){
			$sql = "SELECT `record`.`id`,
					    `record`.`Action`,
					    `record`.`user_id`,
					    `record`.`date` as time,
					    `record`.`date` ,
					    `record`.`ip`,
					    `record`.`desc`,
					    `record`.`tabla`,
					    DATEDIFF(NOW(),record.date) as dias,
						TIME_TO_SEC(TIMEDIFF(NOW(),record.date)) / 60 / 60 as horas,
						TIME_TO_SEC(TIMEDIFF(NOW(),record.date)) / 60 as mins,
						TIME_TO_SEC(TIMEDIFF(NOW(),record.date)) as secs
					FROM `record`
					 WHERE ";

			return $sql;
		}

		function getActives(){
			$searchArray["tabla"] = "evento";
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}
	}

?>
