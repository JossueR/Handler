<?php
	loadClass("models/dao/AbstractBaseDAO.php");
	/**
	 * 
	 */
	class PermissionsDAO extends AbstractBaseDAO {
		
		function __construct() {
			parent::__construct("permissions", array("permission"));
		}
		
		function getPrototype(){
			$prototype = array(
				'permission'=>null,
				'description'=>null
			);
			
			return $prototype;
		}

		
		function getDBMap(){
			$prototype = array(
				'permission'=>'permission',
				'description'=>'description'
			);
			
			return $prototype;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `permissions`.`permission`,
					    `permissions`.`description`
					    
					FROM `permissions`
					WHERE ";
					
			return $sql;
		}
		
		
		function getActives(){
			

			
			$sql = $this->getBaseSelec() . "1=1";
			
				
			$this->find($sql);
		}
		
		function getAsocToUser($user_id){

			$sql = "SELECT `permissions`.`permission`,
					    `permissions`.`description`
					    
					FROM `permissions`
					JOIN user_permissions up on up.permission=`permissions`.`permission`
					WHERE up.user_id=$user_id
					GROUP BY `permissions`.`permission`";
			
				
			$this->find($sql);
		}
		
		function getNotAsocToUser($user_id){

			$sql = "SELECT `permissions`.`permission`,
					    `permissions`.`description`
					    
					FROM `permissions`
					WHERE `permissions`.`permission` not in (
						select  up.permission from user_permissions up WHERE up.user_id=$user_id
					)";
			
				
			$this->find($sql);
		}
		
		function addToUser($user_id, $permission){
			
			$searchArray["user_id"] = $user_id;
			$searchArray["permission"] = $permission;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			$sumary = self::_insert("user_permissions", $searchArray);
			
			return ($sumary->errorNo == 0);
		}
		
		function delToUser($user_id, $permission){
			
			$searchArray["user_id"] = $user_id;
			$searchArray["permission"] = $permission;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			$sumary = self::_delete("user_permissions", $searchArray);
			
			return ($sumary->errorNo == 0);
		}
		
		function getAsocToRol($rol_id){

			$sql = "SELECT `permissions`.`permission`,
					    `permissions`.`description`
					    
					FROM `permissions`
					JOIN group_permissions gu on gu.permission=`permissions`.`permission`
					WHERE gu.group_id=$rol_id
					GROUP BY `permissions`.`permission`";
			
				
			$this->find($sql);
		}
		
		function getNotAsocToRol($rol_id){

			$sql = "SELECT `permissions`.`permission`,
					    `permissions`.`description`
					    
					FROM `permissions`
					WHERE `permissions`.`permission` not in (
						select  gu.permission from group_permissions gu WHERE gu.group_id=$rol_id
					)";
			
				
			$this->find($sql);
		}
		
		function addToRol($rol_id, $permission){
			
			$searchArray["group_id"] = $rol_id;
			$searchArray["permission"] = $permission;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			$sumary = self::_insert("group_permissions", $searchArray);
			
			return ($sumary->errorNo == 0);
		}
		
		function delToRol($rol_id, $permission){
			
			$searchArray["group_id"] = $rol_id;
			$searchArray["permission"] = $permission;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			$sumary = self::_delete("group_permissions", $searchArray);
			
			return ($sumary->errorNo == 0);
		}
		
	}
	
?>