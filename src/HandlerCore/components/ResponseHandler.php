<?php
namespace HandlerCore\components;

use Exception;
use HandlerCore\models\dao\TrackLogDAO;
use HandlerCore\models\SimpleDAO;
use SimpleXMLElement;
use function HandlerCore\getRealIpAddr;

/**
 * Clase base para construir controladores de APIs que manejan respuestas y errores.
 */
class ResponseHandler extends Handler {
    /**
     * Clave para el estado de la respuesta.
     */
    const KEY_STATUS = "status";

    /**
     * Clave para el código de estado de la respuesta.
     */
    const KEY_STATUS_CODE = "status_code";

    /**
     * Clave para el token de acceso en la respuesta.
     */
    const KEY_ACCESS_TOKEN = "access_token";

    /**
     * Clave para el token del cliente en la respuesta.
     */
    const KEY_CLIENT_TOKEN = "client_id";

    /**
     * Clave para los errores en la respuesta.
     */
    const KEY_ERRORS = "errors";

    /**
     * Clave para las advertencias en la respuesta.
     */
    const KEY_WARNING = "warning";

    /**
     * @var mixed Advertencia en la respuesta.
     */
    protected $warning;

    /**
     * @var bool Indica si el estado ha sido añadido a la respuesta.
     */
    private $status_added;

    /**
     * @var string|null Identificador de log.
     */
    private static $log_id;

    /**
     * @var bool Indica si el log está habilitado.
     */
    private static $log_enabled;

    /**
     * Indica que están habilitados el registro de llamados
     * @param $mode
     * @return void
     */
	public static function setLogEnabled($mode){
		self::$log_enabled = $mode;
	}

    /**
     * Constructor de la clase ResponseHandler.
     */
	function __construct(){
		$this->status_added = false;
		SimpleDAO::escaoeHTML_OFF();
		$this->configErrorHandler();

		//si no hay id de log, osea si es el primer llamado
		if(!self::$log_id){
			$this->storelog();
		}
	}

    /**
     * Genera la representación JSON de la respuesta y la envía si es necesario.
     *
     * @param bool $send Indica si se debe enviar la respuesta JSON.
     * @param bool $headers Indica si se deben incluir las cabeceras HTTP para JSON.
     * @return string La representación JSON de la respuesta.
     */
	function toJSON($send = true, $headers = true): string
    {
		$json = "";

		if($send && $headers){
			header('Cache-Control: no-cache, must-revalidate');
			header('Content-type: application/json');
		}

		$this->setGlobalWarning();
		$this->sendWarnging();

		//si no se ha puesto el status
		if(!$this->status_added){
			$this->addStatus();
		}

		$json = json_encode($this->getAllVars());

		if($send){
			//$this->cleanSession();
			$this->storelog($json);
			echo $json;

			exit;
		}

		return $json;

	}

    /**
     * Genera la representación XML de la respuesta y la envía si es necesario.
     *
     * @param bool $send Indica si se debe enviar la respuesta XML.
     * @param bool $headers Indica si se deben incluir las cabeceras HTTP para XML.
     * @return string La representación JSON de la respuesta.
     * @throws Exception
     */
	function toXML($root = "<root/>", $send = true, $headers = true): string
    {
		$resmonse_xml = "";

		$data = $this->getAllVars();
		$xml = new SimpleXMLElement($root);
		foreach ($data as $key => $value) {
			if(!is_array($value)){
				$xml->addChild($key, $value);
			}
		}


		$resmonse_xml = $xml->asXML();

		if($headers){
			header('Cache-Control: no-cache, must-revalidate');
			header("Content-type: text/xml");
		}

		if($send){
			$json = json_encode($this->getAllVars());
			$this->storelog($json);
			echo $resmonse_xml;
			exit;
		}
		return $resmonse_xml;
	}

    /**
     * Establece el estado de éxito en la respuesta.
     * Agrega el estado y el código de estado a la respuesta, indicando éxito.
     */
	protected function sucess(){
		$this->setVar(ResponseHandler::KEY_STATUS, 'success');
		$this->setVar(ResponseHandler::KEY_STATUS_CODE, '100');
		$this->status_added = true;
	}

    /**
     * Establece el estado de error en la respuesta, indicando un error del servidor.
     *
     * @param string $errorCode El código de error del servidor.
     */
	protected function serverError($errorCode = '500'){
		$this->setVar(ResponseHandler::KEY_STATUS, 'server_error');
		$this->setVar(ResponseHandler::KEY_STATUS_CODE, $errorCode);

		$this->setVar(ResponseHandler::KEY_ERRORS,  $this->errors);
		$this->status_added = true;
	}

    /**
     * Agrega advertencias a la respuesta JSON si hay advertencias presentes.
     */
	private function sendWarnging(){
		if($this->warning != null && count($this->warning) > 0){
			$this->setVar(ResponseHandler::KEY_WARNING,  $this->warning);
		}
	}

    /**
     * Agrega una advertencia a la lista de advertencias.
     *
     * @param string $msg El mensaje de advertencia a agregar.
     */
	public function addWarning($msg){
		$this->warning[] = $msg;
	}

    /**
     * Agrega el estado a la respuesta en función de si hay errores o no.
     * Si hay errores, se establece un estado de error; de lo contrario, se establece un estado de éxito.
     */
	protected function addStatus(){
		//si hay errores
		if($this->haveErrors()){
			$this->serverError();
		}else{
			$this->sucess();
		}
	}

    /**
     * Limpia la sesión actual eliminando las cookies de sesión y destruyendo la sesión.
     */
	protected function cleanSession(){
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
		session_destroy();
		session_unset();
	}

    /**
     * Almacena un registro de log en la base de datos, registrando detalles de la solicitud y la respuesta.
     * @param string|null $resp Respuesta que se almacenará en el log.
     */
	public function storelog($resp = null){

		//si esta habilitado el modo log
		if(self::$log_enabled){
			$log = new TrackLogDAO();

			$log_record = array(
				"user"=>"",
				"ip"=>getRealIpAddr(),
				"get"=>json_encode(Handler::getAllRequestData(false)),
				"post"=>json_encode(Handler::getAllRequestData()),
				"resp"=>$resp,
				"_handler" => self::$handler,
				"_do" => self::$do
			);

			//agrega id si es una edicion
			if(self::$log_id){
				$log_record["id"] = self::$log_id;
			}

			//guarda el log
			if($log->save($log_record)){

				//si hay un id nuevo
				if($log->getNewID()){

					//almacena
					self::$log_id = $log->getNewID();
				}
			}
		}

	}

    /**
     * Establece el estado y el código de estado en la respuesta.
     * @param string $status Estado a establecer en la respuesta.
     * @param string $code Código de estado a establecer en la respuesta.
     */
	protected function setStatus($status, $code): void
    {
		$this->setVar(ResponseHandler::KEY_STATUS, $status);
		$this->setVar(ResponseHandler::KEY_STATUS_CODE, $code);


		$this->status_added = true;
	}

    /**
     * Configura un manejador personalizado de errores para capturar y almacenar los errores en la sesión.
     */
	private function configErrorHandler(): void
    {
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
		    // error was suppressed with the @-operator
		    Handler::$SESSION["XERR"][] = "$errno, $errstr, $errfile, $errline";
		});
	}

    /**
     * Establece las advertencias globales basadas en los errores almacenados en la sesión.
     */
	private function setGlobalWarning(): void
    {
		if(isset(Handler::$SESSION["XERR"]) && count(Handler::$SESSION["XERR"]) > 0){
			foreach (Handler::$SESSION["XERR"] as $key => $msg) {
				$this->warning[] = $msg;
			}
		}
	}
}

