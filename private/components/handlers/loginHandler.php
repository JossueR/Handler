<?php
	//include(PATH_PRIVATE . 'Libs/adLDAP/src/adLDAP.php');
	/**
	 * 
	 */
	class loginHandler extends Handler {
		
		function indexAction(){
			$e = $this->getRequestAttr("error",false);
					
			$this->display("views/login/login.php", array("error"=> $e));
		}
		
		function loginAction(){
			$uname = $this->getRequestAttr("user");
			$pass = $this->getRequestAttr("pass");
			$valid = false;
			
			$sql = "select uid, count(uid) as valid, password, LDAP FROM users where active=1 and username = '$uname'";
			
			$user_data = SimpleDAO::execAndFetch($sql);

			
			if($user_data["valid"] == '1' ){
				
				if($user_data["LDAP"] == '1'){
					/* try {
					    $adldap = new adLDAP();
			        }
			        catch (adLDAPException $e) {
			            echo $e; 
			            exit();   
			        }
					
					if ($adldap->authenticate($uname, $pass)){
						$valid= true;
					}*/
				}else{
					if($user_data["password"] == md5($pass)){
						$valid= true;
					}
				}
				
				
				if($valid){
					//cargaa datos de session
					$_SESSION['USER_ID'] = $user_data["uid"];
					$_SESSION["usuario_nombre"] =  "(" . $uname . ")";
					$_SESSION['USER_NAME'] = $uname;
					
					
					
					$sql = "select permission from group_permissions where group_id in 
							(select group_id FROM group_users where user_id='".$user_data["uid"]."')
							UNION
							( SELECT permission FROM user_permissions WHERE user_id='".$user_data["uid"]."' )";
					$sumary = SimpleDAO::execQuery($sql);
					$permisos = SimpleDAO::getAll($sumary);
					$_SESSION['USER_PERMISSIONS'] = array();
					foreach ($permisos as $value) {
						echo $value["permission"];
						$_SESSION['USER_PERMISSIONS'][] = $value["permission"];
					}
					
					$this->windowReload("home");
				}else{
					$this->windowReload("login?error=t");
				}
				
				
			}else{
				$this->windowReload("login?error=t");
			}
			
			
		}
		
		function logoutAction(){
			session_destroy();
			session_unset();
					
			$this->windowReload("login");
		}
	}
	
?>