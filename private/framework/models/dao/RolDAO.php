<?php
	loadClass("models/dao/AbstractBaseDAO.php");
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

		
		function getDBMap(){
			$prototype = array(
				'id'=>'id',
				'name'=>'name'
			);
			
			return $prototype;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `groups`.`id`,
					    `groups`.`name`
					    
					FROM `groups`
					WHERE ";
					
			return $sql;
		}
		
		
		function getActives(){
			

			
			$sql = $this->getBaseSelec() . "1=1";
			
				
			$this->find($sql);
		}
		
		function getAsocToUser($user_id){

			$sql = "SELECT `groups`.`id`,
					    `groups`.`name`
					    
					FROM `groups`
					JOIN group_users gu on gu.group_id=`groups`.`id`
					WHERE gu.user_id=$user_id
					GROUP BY `groups`.`id`";
			
				
			$this->find($sql);
		}
		
		function getNotAsocToUser($user_id){

			$sql = "SELECT `groups`.`id`,
					    `groups`.`name`
					    
					FROM `groups`
					WHERE `groups`.`id` not in (
						select  gu.group_id from group_users gu WHERE gu.user_id=$user_id
					)";
			
				
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