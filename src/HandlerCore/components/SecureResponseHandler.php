<?php
namespace HandlerCore\components;

use HandlerCore\components\handlers\LoginHandler;
use HandlerCore\models\dao\ConnectionFromDAO;
use HandlerCore\models\SimpleDAO;
use function HandlerCore\getRealIpAddr;
use function HandlerCore\showMessage;

/**
 * Clase base para construir controladores de APIs seguras que requieren autenticación previa con un token de acceso.
 * Extiende la clase ResponseHandler y proporciona funcionalidad adicional para la autenticación y seguridad.
 */
class SecureResponseHandler extends ResponseHandler {


    /**
     * Token de acceso utilizado para autenticar las solicitudes a la API segura.
     * Este token debe ser proporcionado en cada solicitud para verificar la autenticación.
     *
     * @var string|null
     */
	protected $access_token;

    /**
     * Proporciona información detallada sobre el usuario autenticado.
     *
     * @var mixed|null
     */
	protected $user;

    /**
     * Nombre de usuario del usuario autenticado.
     *
     * @var string|null
     */
	protected $username;

    /**
     * ID del cliente relacionado con el usuario autenticado.
     *
     * @var int|null
     */
	protected $customer_id;

    /**
     * Datos de conexión utilizados para la autenticación.
     * Contiene información adicional relacionada con la conexión segura.
     *
     * @var mixed|null
     */
	protected $connection_data;

    /**
     * Modo de verificación de dirección IP para mayor seguridad.
     * Esta propiedad estática permite configurar el modo de verificación de IP.
     *
     * @var mixed
     */
	private static $ip_check_mode;

    /**
     * Establece el modo de verificación de dirección IP para la autenticación segura.
     *
     * @param mixed $mode Modo de verificación de IP.
     * @return void
     */
	public static function setIPcheckMode($mode){
		self::$ip_check_mode = $mode;
	}


    /**
     * Constructor de la clase SecureResponseHandler.
     * Realiza la autenticación y establece la información de usuario y acceso.
     */
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

    /**
     * Obtiene el acceso autenticado y los datos de conexión necesarios para la seguridad de la API.
     *
     * @return void
     */
	protected function getAccess(): void
    {
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
		$connection_data = $conn->get();

		//si hay token
		if(!$token || $connection_data["token"] != $token ||  $token == ""){

			$this->addError(showMessage("error_invalid_token"));

			$this->setStatus('access_denied', '400');
			//$this->addWarning($conn->getSumary()->sql);
		}else{


			$this->access_token = $connection_data["token"];
			$this->username = $connection_data["user"];
			$this->customer_id = $connection_data["customer_id"];
			$this->connection_data = $connection_data;

			SimpleDAO::setDataVar("USER_NAME", $this->username );
		}

	}

    /**
     * Carga los datos de sesión y configuración relacionados con el usuario autenticado.
     *
     * @return void
     */
	protected function loadSession(): void
    {

		//usa la session por defecto
		LoginHandler::loadPermissions($this->connection_data["user_id"]);
        LoginHandler::loadConf();
	}

    /**
     * Establece el nombre de usuario en la sesión si aún no está configurado.
     *
     * @return void
     */
    protected function  setUsername(): void
    {
		if(!isset(Handler::$SESSION['USER_NAME'] )){

			Handler::$SESSION['USER_ID'] = $this->connection_data["user_id"];
			Handler::$SESSION['USER_NAME'] = $this->username;
		}


	}

    /**
     * Actualiza el registro "last" en la tabla de conexiones.
     *
     * @return void
     */
    protected function updateLast(): void
    {

		//actualiza last en la coneccion
		$conn = new ConnectionFromDAO();
		$conn->updateLast(array("id" => $this->connection_data["id"]));
	}
}
