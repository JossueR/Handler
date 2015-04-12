<?php
	loadClass("models/dao/AbstractBaseDAO.php");
	/**
	 * 
	 */
	class UsersDAO extends AbstractBaseDAO {
		const LOGIN_LDAP = 1;
		const LOGIN_LOCAL = 0;
		
		function __construct() {
			parent::__construct("users", array("uid"));
		}
		
		function getPrototype(){
			$prototype = array(

				'username'=>null,
				'LDAP'=>null,
				'f_nacimiento'=>null,
				'nombre'=>null,
				'apellidos'=>null,
				'sexo'=>null,
				'password'=>null
			);
			
			return $prototype;
		}

		
		function getDBMap(){
			$prototype = array(
				'uid'=>'uid',
				'username'=>'username',
				'LDAP'=>'LDAP',
				'f_nacimiento'=>'f_nacimiento',
				'nombre'=>'nombre',
				'apellidos'=>'apellidos',
				'sexo'=>'sexo',
				'active' =>'active',
				'password'=>'password'
			);
			
			return $prototype;
		}
		
		function getBaseSelec(){
			$sql = "SELECT `users`.`uid`,
					    `users`.`username`,
					    `users`.`email`,
					    '' as `password`,
					    `users`.nombre,
					    `users`.apellidos,
					    `users`.sexo,
					    `users`.f_nacimiento,
					    `users`.`created`,
					    `users`.`lastlogin`,
					    `users`.`LDAP`
					FROM `users`
					WHERE ";
					
			return $sql;
		}
		
		function getActives(){
			$searchArray["username"] = self::$SQL_TAG."IS NOT NULL";
			$searchArray["users.active"] = self::REG_ACTIVO;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}
		
		function getInactives(){
			$searchArray["username"] = self::$SQL_TAG."IS NOT NULL";
			$searchArray["users.active"] = self::REG_DESACTIVADO;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);
			
			$sql = $this->getBaseSelec() . $where;
			
				
			$this->find($sql);
		}

		
		function &insert($searchArray){
			$defaul["created"] = self::$SQL_TAG."now()";
			$defaul["active"] = self::REG_ACTIVO;
			$defaul = parent::putQuoteAndNull($defaul);
			
			$searchArray = array_merge($searchArray, $defaul);
			

			return parent::insert($searchArray);
			
		}
		
		function &update($searchArray, $condicion){
			return parent::update($searchArray, $condicion);
			
		}
		
		function getAsocToRol($rol_id){

			$sql = "SELECT `users`.`uid`,
					    `users`.`username`,
					    `users`.nombre,
					    `users`.apellidos
					    
					FROM `users`
					JOIN group_users gu on gu.user_id=`users`.`uid`
					WHERE gu.group_id=$rol_id
					AND `users`.active = '".self::REG_ACTIVO."'
					GROUP BY `users`.`uid`";
			
				
			$this->find($sql);
		}
		
		function getNotAsocToRol($rol_id){

			$sql = "SELECT `users`.`uid`,
					    `users`.`username`,
					    `users`.nombre,
					    `users`.apellidos
					    
					FROM `users`
					WHERE `users`.`uid` not in (
						select  gu.user_id from group_users gu WHERE gu.group_id=$rol_id
					)
					AND `users`.active = '".self::REG_ACTIVO."'";
			
				
			$this->find($sql);
		}
		
		function addToRol($user_id, $rol_id){
			
			$searchArray["user_id"] = $user_id;
			$searchArray["group_id"] = $rol_id;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			$sumary = self::_insert("group_users", $searchArray);
			
			return ($sumary->errorNo == 0);
		}
		
		function delToRol($user_id, $rol_id){
			
			$searchArray["user_id"] = $user_id;
			$searchArray["group_id"] = $rol_id;
			$searchArray = parent::putQuoteAndNull($searchArray);
			
			$sumary = self::_delete("group_users", $searchArray);
			
			return ($sumary->errorNo == 0);
		}

	}
	
?>