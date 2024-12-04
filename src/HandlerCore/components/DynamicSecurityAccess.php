<?php
namespace HandlerCore\components;


use Closure;
use HandlerCore\Environment;
use HandlerCore\models\dao\ConfigVarDAO;
use HandlerCore\models\dao\SecAccessDAO;

/**
 * La clase DynamicSecurityAccess permite gestionar dinámicamente los accesos a través de permisos.
 */
class DynamicSecurityAccess {
    /**
     * Clave para el almacenamiento de las reglas de acceso en la sesión.
     */
	const RULES = "RULES";

    private static $separator = "::";
    private static array $permissionList = [];
    private static bool $permissionStorageModeSession = true;
    /**
     * Instancia de SecAccessDAO para acceder a los datos de permisos.
     * @var SecAccessDAO
     */
	private $dao;
	private $permission;

	public static $show_names;

    /**
     * Constructor de la clase DynamicSecurityAccess.
     * Crea una instancia de la clase y carga las reglas de acceso si no están en la sesión.
     */
	function __construct() {
		if(!isset($_SESSION[self::RULES])){
			$_SESSION[self::RULES] = array();
 		}

		$this->dao = new SecAccessDAO();
	}

    /**
     * Clausura que se ejecuta cuando se deniega un permiso.
     *
     * Esta propiedad permite configurar una clausura (closure) que se ejecutará
     * cuando un permiso es denegado por las reglas de acceso. La clausura recibe el
     * permiso denegado y puede utilizarse para definir una acción específica que se
     * realizará en caso de acceso no autorizado.
     *
     * @var Closure|null $onPermissionDenny Permiso denegado clausura.
     */
    public static ?Closure $onPermissionDenny = null;

    /**
     * Clausura que se ejecuta cuando se deniega un permiso.
     *
     * Esta propiedad permite configurar una clausura (closure) que se ejecutará
     * cuando un permiso es denegado por las reglas de acceso. La clausura recibe
     * el invocador y el permiso. Puede utilizarse para definir una acción específica que se
     * realizará en caso de mostrar el nombre del invocador
     *
     * @var Closure|null $onShowInvokerName Permiso denegado clausura.
     */
    public static ?Closure $onShowInvokerName = null;

    /**
     * Limpia las reglas de acceso almacenadas en la sesión.
     *
     * Este método estático limpia las reglas de acceso almacenadas en la sesión,
     * reinicializando el array de reglas de acceso.
     *
     * @return void
     */
	public static function cleanRules(){
		if(isset($_SESSION[self::RULES])){
			unset($_SESSION[self::RULES]);
		}
		$_SESSION[self::RULES] = array();
	}



    /**
     * Verifica si el usuario tiene un permiso específico.
     *
     * Este método estático verifica si el usuario actual tiene un permiso específico.
     * Si la validación de permisos está habilitada y el permiso se encuentra en la lista
     * de permisos del usuario, el método devuelve verdadero. Si el permiso no se encuentra,
     * puede ejecutar una acción definida en `onPermissionDenny`.
     *
     * @param string $permission El permiso que se desea verificar.
     * @return bool Verdadero si el usuario tiene el permiso, falso de lo contrario.
     */
	public static function havePermission(string $permission): bool
	{
		$check = true;

		//sí está habilitada la validación de permisos
		if(self::getPermissionCheck() && $permission != null && $permission != ""){
			$check = in_array($permission, self::getLoadedPermissions());

			if(!$check){
				//echo "#####################$permission";
                if(!is_null(self::$onPermissionDenny)){
                    $callback = self::$onPermissionDenny;
                    $callback($permission);
                }
			}
		}


		return $check;
	}

    public static function havePermissionStrictCheck(string $permission): bool
    {
        $check = false;

        //sí está habilitada la validación de permisos
        if(!empty($permission)){
            $check = in_array($permission, self::getLoadedPermissions());

            if(!$check){
                //echo "#####################$permission";
                if(!is_null(self::$onPermissionDenny)){
                    $callback = self::$onPermissionDenny;
                    $callback($permission);
                }
            }
        }


        return $check;
    }

    public static function loadPermissions(array $permissions, bool $inSession = true): void
    {
        self::$permissionStorageModeSession = $inSession;
        if(self::$permissionStorageModeSession){
            $_SESSION['USER_PERMISSIONS'] = $permissions;
        }else{
            self::$permissionList = $permissions;
        }


    }

    private static function getLoadedPermissions(){
        return self::$permissionStorageModeSession? $_SESSION['USER_PERMISSIONS'] : self::$permissionList;
    }

    /**
     * Obtiene el estado de la verificación de permisos.
     *
     * Este método estático devuelve el estado actual de la verificación de permisos,
     * consultando la configuración almacenada en la sesión.
     *
     * @return bool El estado de la verificación de permisos.
     */
	public static function getPermissionCheck(): bool
    {
        return $_SESSION["CONF"][ConfigVarDAO::VAR_PERMISSION_CHECK];
    }

    /**
     * Obtiene el estado de habilitación de seguridad de registros.
     *
     * Este método estático obtiene el estado de habilitación de seguridad de registros,
     * consultando la configuración almacenada en la sesión.
     *
     * @return bool El estado de habilitación de seguridad de registros.
     */
	public static function getEnableRecordSecurity(): bool
    {
		$r = false;
		if(isset($_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_RECORD_SECURITY])){
			$r = $_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_RECORD_SECURITY];
		}
        return $r;
    }

    /**
     * Obtiene el estado de habilitación de seguridad de acciones de handler.
     *
     * Este método estático obtiene el estado de habilitación de seguridad de acciones de handler,
     * consultando la configuración almacenada en la sesión.
     *
     * @return bool El estado de habilitación de seguridad de acciones de handler.
     */
	public static function getEnableHandlerActionSecurity(): bool
    {
		$r = false;
		if(isset($_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_HANDLER_ACTION_SECURITY])){
			$r = $_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_HANDLER_ACTION_SECURITY];
		}
        return $r;
    }

    /**
     * Obtiene el estado de habilitación de seguridad de Dashboards.
     *
     * Este método estático obtiene el estado de habilitación de seguridad de Dashboards,
     * consultando la configuración almacenada en la sesión.
     *
     * @return bool El estado de habilitación de seguridad de Dashboards.
     */
	public static function getEnableDashSecurity(): bool
    {
        return $_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_DASH_SECURITY];
    }

    /**
     * Obtiene el estado de habilitación de seguridad de botones en Dashboards.
     *
     * Este método estático obtiene el estado de habilitación de seguridad de botones en Dashboards,
     * consultando la configuración almacenada en la sesión.
     *
     * @return bool El estado de habilitación de seguridad de botones en Dashboards.
     */
	public static function getEnableDashButtonSecurity(): bool
    {
        return $_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_DASH_BUTTON_SECURITY];
    }


    /**
     * Carga las reglas de acceso desde la base de datos o la memoria caché.
     *
     * Este método privado carga las reglas de acceso para un invocador y un método específicos.
     * Si las reglas no están almacenadas en la memoria, se obtienen de la base de datos utilizando
     * el DAO correspondiente. Si se especifica un método, se cargan todas las reglas asociadas a ese
     * método. Las reglas se almacenan en la memoria caché para su posterior verificación.
     *
     * @param string $invoker El invocador para el que se cargan las reglas.
     * @param string|null $method El método para el que se cargan las reglas (opcional).
     * @return void
     */
	private function loadRules($invoker, $method = null): void
    {
		//si no esta en memoria la regla
		if(!isset($_SESSION[self::RULES][$invoker])){
			//si no se espeso foco validar
			if(!$method){
				//carga la regla a memoria
				 $this->dao->getById(array("invoker"=>$invoker));
				 $r = $this->dao->get();


				 $this->setPermission($invoker, $r["permission"]);
			}else{
				//carga todas las reglas del dash
				 $this->dao->getMethodRules($method);
				 //var_dump($this->dao->getSumary()->sql);
				 while ($r = $this->dao->get()) {

					$this->setPermission($r["invoker"], $r["permission"]);
				 }
			}

		}
	}

    /**
     * Verifica el acceso basado en un permiso.
     *
     * Este método privado verifica si el acceso es válido basado en un permiso. Si el permiso no está
     * vacío, se verifica a través de `havePermission`. Si no hay acceso, se registra el permiso denegado;
     * de lo contrario, se limpia el permiso. El método devuelve verdadero si hay acceso y falso si no lo hay.
     *
     * @param string $permission El permiso que se verifica.
     * @return bool Verdadero si hay acceso, falso si no lo hay.
     */
	private function check($permission): bool
    {
		//siempre hay acceso por defecto
		$access = true;

		//si tiene permiso configurado
		if($permission != ""){
			//valida el permiso
			$access = self::havePermission($permission);
		}

		//si no tiene el permiso
		if(!$access){
			//registra el permiso fallido
			$this->permission = $permission;
		}else{
			//limpia el permiso
			$this->permission = null;
		}

		return $access;
	}

    /**
     * Verifica el acceso a una acción de handler controlador.
     *
     * Este método verifica si el acceso a una acción de handler está permitido. Verifica
     * si está habilitada la revisión de permisos de las acciones y carga las reglas correspondientes.
     * Si se habilita el registro de seguridad, se registra el acceso. El método devuelve verdadero
     * si el acceso está permitido y falso si no lo está.
     *
     * @param string $handler El nombre del handler.
     * @param string $action El nombre de la acción.
     * @return bool Verdadero si el acceso está permitido, falso si no lo está.
     */
	public function checkHandlerActionAccess($handler, $action): bool
    {

		$access = true;


		$invoker = $handler . self::$separator . $action;


		if(self::getEnableRecordSecurity()){
			$this->Record($invoker);
		}


		if(self::getEnableHandlerActionSecurity()){


			$this->loadRules($invoker);


			$permission = $this->getPermission($invoker);

            $this->showInvokerName($invoker, $permission); // Muestra el nombre del invocador y el permiso


			//valida
			$access = $this->check($permission);
		}


		return $access;
	}

    /**
     * Verifica el acceso a un elemento en un Dash.
     *
     * Este método verifica si el acceso a un elemento en un Dashboard está permitido. Verifica
     * si está habilitada la revisión dinámica de permisos y carga las reglas correspondientes.
     * Si se habilita el registro de seguridad, se registra el acceso. El método devuelve verdadero
     * si el acceso está permitido y falso si no lo está.
     *
     * @param string $method El método del elemento en el Dashboard.
     * @param string $name El nombre del elemento en el Dashboard.
     * @return bool Verdadero si el acceso está permitido, falso si no lo está.
     */
	public function checkDash($method, $name): bool
    {


		$access = true; // Siempre hay acceso por defecto

		if($method && $method != ""){

			$invoker = $method . self::$separator . $name; // Obtiene el nombre completo del objeto


			if(self::getEnableRecordSecurity()){
				$this->Record($invoker, $method); // Registra el acceso si está habilitado el registro
			}





			if(self::getEnableDashSecurity()){

				$this->loadRules($invoker,$method); // Carga las reglas si está habilitada la revisión dinámica


				$permission = $this->getPermission($invoker); // Obtiene el permiso

				$this->showInvokerName($invoker, $permission); // Muestra el nombre del invocador y el permiso


				$access = $this->check($permission); // Valida el acceso
			}
		}



		return $access;
	}

    /**
     * Verifica el acceso a un botón en un Dashboard.
     *
     * Este método verifica si el acceso a un botón en un Dashboard está permitido. Verifica
     * si está habilitada la revisión dinámica de permisos para botones y carga las reglas correspondientes.
     * Si se habilita el registro de seguridad, se registra el acceso. El método devuelve verdadero
     * si el acceso está permitido y falso si no lo está.
     *
     * @param string $method El método del elemento en el Dashboard.
     * @param string $name El nombre del elemento en el Dashboard.
     * @param string $btn El nombre del botón.
     * @return bool Verdadero si el acceso está permitido, falso si no lo está.
     */
	public function checkDashButton($method, $name, $btn): bool
    {

		$access = true; // Siempre hay acceso por defecto

		if($method && $method != ""){
			//obtiene el nombre completo del objeto
			$invoker = $method . self::$separator . $name . self::$separator . $btn;


			if(self::getEnableRecordSecurity()){
				$this->Record($invoker, $method); // Registra el acceso si está habilitado el registro
			}

			//si esta activa la revision dinamica de permisos
			if(self::getEnableDashButtonSecurity()){

				$this->loadRules($invoker,$method); //Carga las reglas si está habilitada la revisión dinámica


				$permission = $this->getPermission($invoker); // Obtiene el permiso

                $this->showInvokerName($invoker, $permission); // Muestra el nombre del invocador y el permiso


				$access = $this->check($permission); // Valida el acceso
			}
		}



		return $access;
	}

    /**
     * Registra un acceso en la base de datos.
     *
     * Este método privado registra un acceso en la base de datos, almacenando el invocador y el método
     * correspondientes. Si no se especifica un método, se considera el invocador como el método.
     * Se verifica si ya existe un registro similar antes de almacenar uno nuevo.
     *
     * @param string $invoker El invocador del acceso.
     * @param string|null $method El método del acceso (opcional).
     * @return void
     */
	private function Record($invoker, $method= null){
		if(!$method){
			$method = $invoker;
		}

		$d = array(
			"invoker" => $invoker,
			"method" => $method
		);
		//si no existe ya registrado
		if(!$this->dao->exist($this->dao->putQuoteAndNull($d))){
			$this->dao->save($d);
		}
	}

    /**
     * Obtiene el permiso que falló en la verificación.
     *
     * Este método devuelve el permiso que falló en la verificación cuando se deniega el acceso.
     *
     * @return string|null El permiso que falló, o nulo si el acceso se concedió.
     */
	public function getFailPermission(){
		return $this->permission;
	}

    /**
     * Obtiene el permiso asociado a un invocador.
     *
     * Este método privado devuelve el permiso asociado a un invocador si está almacenado en la memoria.
     *
     * @param string $invoker El invocador para el que se obtiene el permiso.
     * @return string El permiso asociado al invocador, o cadena vacía si no está almacenado.
     */
	public function getPermission($invoker){
		$permisssion = "";

		if(isset($_SESSION[self::RULES]) && isset($_SESSION[self::RULES][$invoker])){
			$permisssion = $_SESSION[self::RULES][$invoker];
		}
		return $permisssion;
	}

    /**
     * Establece el permiso asociado a un invocador.
     *
     * Este método privado establece el permiso asociado a un invocador en la memoria.
     *
     * @param string $invoker El invocador para el que se establece el permiso.
     * @param string $permission El permiso que se establece.
     * @return void
     */
	private function setPermission($invoker, $permission){
		$_SESSION[self::RULES][$invoker] = $permission;
	}

    /**
     * Muestra el nombre del invocador y el permiso si la opción está habilitada.
     *
     * Este método privado muestra el nombre del invocador y el permiso asociado si la opción para
     * mostrar los nombres está habilitada. Crea un enlace que, cuando se hace clic, carga un formulario
     * de SecAccess en el contenido principal de la aplicación para editar las reglas de acceso del invocador.
     *
     * @param string $invoker El invocador del que se muestra el nombre.
     * @param string $permission El permiso asociado al invocador.
     * @return void
     */
	private function showInvokerName($invoker, $permission): void
    {

		if(self::$show_names == null){
			self::$show_names = false;

		}

		if(self::$show_names){

            if(!is_null(self::$onShowInvokerName)){
                $callback = self::$onShowInvokerName;
                $callback($invoker, $permission);
            }else {


                $link = Handler::make_link("<h5><i class='fas fa-lock'></i> invoker: $invoker</h5>",
                    Handler::asyncLoad("SecAccess", Environment::$APP_CONTENT_BODY, array(
                        "do" => "form",
                        "invoker" => $invoker
                    ), true),
                    false
                );
                echo "<div class='col-12'>
					<div class='callout callout-danger'>
						$link
						$permission
					</div>
				</div>";
            }
		}

	}
}


