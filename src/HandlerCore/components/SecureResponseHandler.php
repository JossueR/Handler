<?php
namespace HandlerCore\components;

use HandlerCore\components\handlers\LoginHandler;
use HandlerCore\models\dao\ConnectionFromDAO;
use HandlerCore\models\SimpleDAO;
use function HandlerCore\getRealIpAddr;
use function HandlerCore\showMessage;

/**
 * Handler que carga las funciones para responder por JSON
 */
class SecureResponseHandler extends ResponseHandler {


	protected $access_token;
	protected $user;
	protected $username;
	protected $customer_id;
	protected $connection_data;
	private static $ip_check_mode;

	public static function setIPcheckMode($mode){
		self::$ip_check_mode = $mode;
	}


	function __construct(){
		parent::__construct();
		$this->getAccess();




		//si hay errores
		if($this->haveErrors()){
			//envía errores y termina
			$this->toJSON();
		}else{
			$this->loadSession();
			$this->setUsername();
			$this->updateLast();
		}
	}

	function getAccess(){
		$uname = $this->getRequestAttr("user");
		$token = $this->getRequestAttr("token");

		//$ip = null;
		$conn = new ConnectionFromDAO();

		//si esta habilitado el verificar ip
		if(self::$ip_check_mode){
			$ip = getRealIpAddr();
		}else{
			$ip = null;
		}

		//busca un token valido
		$conn->getValidToken($uname, $ip);
		$conection_data = $conn->get();

		//si hay token
		if(!$token || $conection_data["token"] != $token ||  $token == ""){

			$this->addError(showMessage("error_invalid_token"));

			$this->setStatus('access_denied', '400');
			//$this->addWarning($conn->getSumary()->sql);
		}else{


			$this->access_token = $conection_data["token"];
			$this->username = $conection_data["user"];
			$this->customer_id = $conection_data["customer_id"];
			$this->connection_data = $conection_data;

			SimpleDAO::setDataVar("USER_NAME", $this->username );
		}

	}

	protected function loadSession(){

		//usa la session por defecto
		LoginHandler::loadPermissions($this->connection_data["user_id"]);
        LoginHandler::loadConf();
	}

    protected function  setUsername(){
		if(!isset(Handler::$SESSION['USER_NAME'] )){

			Handler::$SESSION['USER_ID'] = $this->connection_data["user_id"];
			Handler::$SESSION['USER_NAME'] = $this->username;
		}


	}

    protected function updateLast(){

		//actualiza last en la coneccion
		$conn = new ConnectionFromDAO();
		$conn->updateLast(array("id" => $this->connection_data["id"]));
	}
}
