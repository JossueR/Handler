<?php
namespace HandlerCore\models\dao;
	/**
	 *
	 */
	class RolDAO extends AbstractBaseDAO {

		function __construct() {
			parent::__construct("groups", array("id"));
		}

		function getPrototype(){
			$prototype = array(

				'name'=>null
			);

			return $prototype;
		}

		function getPrototypeFull(){
			$prototype = array(

				'name'=>null,
				"public"=>null,
			);

			return $prototype;
		}


		function getDBMap(){
			$prototype = array(
				'id'=>'id',
				'name'=>'name',
				"public"=>'public',
			);

			return $prototype;
		}

		function getBaseSelec(){
			$sql = "SELECT `groups`.`id`,
					    `groups`.`name`,
					    `groups`.`public`
					    
					FROM `groups`
					WHERE ";

			return $sql;
		}


		function getActives(){



			$sql = $this->getBaseSelec() . "1=1";


			$this->find($sql);
		}

		function getPublicActives(){
			$searchArray["`groups`.`public`"] = self::REG_ACTIVO_Y;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);

		}

		function getAsocToUser($user_id){

			$sql = "SELECT `groups`.`id`,
					    `groups`.`name`,
					    `groups`.`public`
					    
					FROM `groups`
					JOIN group_users gu on gu.group_id=`groups`.`id`
					WHERE gu.user_id=$user_id
					GROUP BY `groups`.`id`";


			$this->find($sql);
		}

		function getNotAsocToUser($user_id, $public=null){
			$sql_public = "";
			if(!$public){

				$sql_public = "and groups.public = '".self::REG_ACTIVO_Y."'";
			}

			$sql = "SELECT `groups`.`id`,
					    `groups`.`name`
					    
					FROM `groups`
					WHERE `groups`.`id` not in (
						select  gu.group_id from group_users gu WHERE gu.user_id=$user_id
					)
					$sql_public";


			$this->find($sql);
		}

		function addToUser($user_id, $rol_id){

			$searchArray["user_id"] = $user_id;
			$searchArray["group_id"] = $rol_id;
			$searchArray = parent::putQuoteAndNull($searchArray);

			$sumary = self::_insert("group_users", $searchArray);

			return ($sumary->errorNo == 0);
		}

		function delToUser($user_id, $rol_id){

			$searchArray["user_id"] = $user_id;
			$searchArray["group_id"] = $rol_id;
			$searchArray = parent::putQuoteAndNull($searchArray);

			$sumary = self::_delete("group_users", $searchArray);

			return ($sumary->errorNo == 0);
		}

	}

?>
