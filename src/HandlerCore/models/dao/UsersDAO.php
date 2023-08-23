<?php
namespace HandlerCore\models\dao;
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

				'f_nacimiento'=>null,
				'nombre'=>null,
				'apellidos'=>null,
				'sexo'=>null,
				'password'=>null
			);

			return $prototype;
		}

		function getPrototypeEdit(){
			$prototype = array(


				'f_nacimiento'=>null,
				'nombre'=>null,
				'apellidos'=>null,
				'sexo'=>null

			);

			return $prototype;
		}

		function getPrototypePass(){
			$prototype = array(
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
					    concat(
					    	'[',
					    	`users`.`username`,
					    	']',
					    	' ',
					    	`users`.nombre,
					    	' ',
					    	`users`.apellidos
					    ) as user_detail,
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

		function getByPermission($permission_id){

			$sql = "select u.uid, u.username, u.nombre, u.apellidos, concat('[',  u.username , '] ',  u.nombre , ' ', u.apellidos) as description
					from users u
					left join user_permissions up on up.user_id=u.uid
					WHERE
					up.permission='$permission_id'
					AND u.active = '".self::REG_ACTIVO."'
					UNION
					select u.uid, u.username, u.nombre, u.apellidos, concat('[',  u.username , '] ',  u.nombre , ' ', u.apellidos) as description
					from users u
					LEFT JOIN group_users gu on gu.user_id=u.uid
					LEFT JOIN group_permissions gp on gp.group_id=gu.group_id
					WHERE
					gp.permission='$permission_id'
					AND u.active = '".self::REG_ACTIVO."'";


			$this->find($sql);
		}

		function getIdByUsername($username){
			$searchArray["username"] = $username;
			$searchArray["users.active"] = self::REG_ACTIVO;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
			$u = $this->get();

			return $u["uid"];
		}

		function getByUsername($username){
			$searchArray["username"] = $username;
			$searchArray["users.active"] = self::REG_ACTIVO;
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);

		}

		function getByClient($client_id){

			$sql = "select u.uid, u.username, u.nombre, u.apellidos
					from users u
					left join client_by_user t1 on t1.username=u.username
					WHERE
					t1.client_id='$client_id'
					AND u.active = '".self::REG_ACTIVO."'
					";


			$this->find($sql);
		}

		function getByClientNotSelected($client_id){

			$sql = "select u.uid, u.username, u.nombre, u.apellidos
					from users u
					WHERE
					u.username not in (
						select t1.username
						from client_by_user t1
						where t1.client_id='$client_id'
					)
					AND u.active = '".self::REG_ACTIVO."'";


			$this->find($sql);
		}

		function validPass($id, $pass){
			$searchArray["users.active"] = self::REG_ACTIVO;
			$searchArray["users.uid"] = $id;
			$searchArray["users.password"] = md5($pass);
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);


			return $this->existBy($searchArray);
		}

	}

?>
