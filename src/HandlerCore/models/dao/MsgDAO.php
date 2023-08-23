<?php
namespace HandlerCore\models\dao;
	/**
	 *
	 */
	class MsgDAO extends AbstractBaseDAO {

		function __construct() {
			parent::__construct("msg", array("msg_id"));
		}

		function getPrototype(){
			$prototype = array(

				'name'=>null,
				'email'=>null,
				'phone'=>null,
				'comment'=>null
			);

			return $prototype;
		}


		function getDBMap(){
			$prototype = array(
				'msg_id'=>'msg_id',
				'name'=>'name',
				'email'=>'email',
				'phone'=>'phone',
				'comment'=>'comment',
				'active' =>'active'
			);

			return $prototype;
		}

		function getBaseSelec(){
			$sql = "SELECT `msg`.`msg_id`,
					    `msg`.`name`,
					    `msg`.`email`,
					    `msg`.`phone`,
					    `msg`.`comment`,
					    `msg`.`active`,
					    `msg`.`createDate`,
					    `msg`.`createUser`,
					    `msg`.`updateDate`,
					    `msg`.`updateUser`
					FROM `msg`
					WHERE ";

			return $sql;
		}


		function getActives(){
			$searchArray["msg.active"] = self::REG_ACTIVO_TX;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}



		function getInactives(){
			$searchArray["msg.active"] = self::REG_DESACTIVADO_TX;
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
