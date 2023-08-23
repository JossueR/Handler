<?php
namespace HandlerCore\models\dao;
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

		function getPrototypeDocument(){
			$prototype = array(

				'document'=>null,

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
				'description'=>'description',
				'filename'=>'filename',
				'document_type'=>'document_type',
				'refference_id'=>'refference_id',
				'refference_type'=>'refference_type',
				'ext'=>'ext'
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
					    `img`.`filename`,
					    `img`.`ext`,
					    `img`.`document_type`,
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

			if($reff_id){
				$searchArray["img.refference_id"] = $reff_id;
			}

			if($type){
				$searchArray["img.type"] = $type;
			}

			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}

		function getByReff($reff_id, $type = null){
			$searchArray["img.refference_id"] = $reff_id;

			if($type){
				$searchArray["img.refference_type"] = $type;
			}

			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}

		function getOthers($reff_type, $reff_id, $id){
			$searchArray["img.refference_type"] = $reff_type;
			$searchArray["img.refference_id"] = $reff_id;

			$searchArray["img.img_id"] = self::$SQL_TAG . "<> '$id' ";


			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}

		function deleteOthers($reff_type, $reff_id, $id){
			$proto["reff_type"] = $reff_type;
			$proto["reff_id"] = $reff_id;

			$proto["img_id"] = self::$SQL_TAG . "<> '$id' ";




			return $this->delete($proto);
		}


		function &insert($searchArray){
			$defaul["createDate"] = self::valueNOW();
			$defaul["createUser"] = self::getDataVar("USER_NAME");

			$defaul = parent::putQuoteAndNull($defaul);

			$searchArray = array_merge($searchArray, $defaul);


			return parent::insert($searchArray);

		}

		function &update($searchArray, $condicion){
			$defaul["updateDate"] = self::valueNOW();
			$defaul["updateUser"] = self::getDataVar("USER_NAME");
			$defaul = parent::putQuoteAndNull($defaul);

			$searchArray = array_merge($searchArray, $defaul);
			return parent::update($searchArray, $condicion);
		}


	}

?>
