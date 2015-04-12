<?php
/**
*Create Date: 07/18/2011 19:04:00
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 201 $
 * Rev:  $LastChangedRevision: 201 $
*/

//Constantes de Aplicacion
	//Titulo de la Aplicacion
	define( 'APP_TITLE', "Casita Admin" );
	
	//Sucursal
	define( 'APP_SUC', "CASITA_ADMIN" );
	
	//Lenguaje de la Aplicacion por defecto
	define( 'APP_LANG', "es" );
	
	//Lenguaje de la Aplicacion por defecto
	define( 'APP_DATE_FORMAT', "DD-MM-YYYY" );
	
	//Formato de fecha de la Base de datos por defecto
	define( 'DB_DATE_FORMAT', "YYYY-MM-DD" );
	
	//Formato para extraer fechas de la base de datos
	define( 'DB_DISPLAY_DATE_FORMAT', "%d-%m-%Y" );
	
	//Formato para extraer fecha y hora de la base de datos
	define( 'DB_DISPLAY_DATETIME_FORMAT', "%d-%m-%Y %h:%i:%s%p" );
	
	//Lenguaje de la Aplicacion por defecto
	define( 'APP_MONEY_WILDCARD', "$" );
	
	//Id del Elemento DOM que contendra el menu principal
	define( 'APP_MAIN_BAR', "main_bar" );
	
	//Id del Elemento DOM que contendra el menu informativo
	define( 'APP_MINI_BAR', "top-search" );
	
	//Id del Elemento DOM que contendra el Titulo de la pantalla
	define( 'APP_CONTENT_TITLE', "page-heading" );
	
	//Id del Elemento DOM que contendra la barra de pasos
	define( 'APP_STEPS_BAR', "tabs_container" );
	
	//Id del Elemento DOM que contendra el cuerpo de la pantalla
	define( 'APP_CONTENT_BODY', "main_content" );
	
	//Id del Elemento DOM que contendra el cuerpo de la pantalla
	define( 'APP_HIDEN_CONTENT', "comon_contend" );
	
	//Id del Elemento DOM que contendra el submenu
	define( 'APP_SUBMENU', "submenu_main" );
	
	//Id del Elemento DOM que contendra la informacion de el usuario logeado
	define( 'APP_USERINFO', "user_info" );
	
	//Id del Elemento DOM que contendra el cuerpo de la pantalla
	define( 'APP_DEFAULT_LIMIT_PER_PAGE', 15);
	
	//default hadler to be displayed
	define('APP_DEFAULT_HANDLER', 'home');
	
	
	//Default TimeZone
	date_default_timezone_set('America/Panama');
	
	
	
//mensajes basicos
	/**
	 * $_SESSION['TAG'] 
	 * es un array que contiene las etiquetas que seran utilizadas en el programa.
	 * se carga en langage.php
	 */
	//si no se han cargado las sesiones, carga los mensajes basicos
	if(!isset($_SESSION['TAG'])){
		$_SESSION['TAG']['login'] = "Login";
		$_SESSION['TAG']['user'] = "User";
		$_SESSION['TAG']['pass'] = "Pass";
		$_SESSION['TAG']['bad_login'] = "Nombre de Usuario o Contraseña incorrecto";
		$_SESSION['TAG']['bad_conection'] = "problemas de coneccion";
	}
	
//inisializa la variable que almacenara las acciones pasadas del usuario
	if(!isset($_SESSION['HISTORY'])){
		$_SESSION["HISTORY"] = array();
	}
	
		
	
//Carga Funciones Basicas
	include(PATH_FRAMEWORK . "microKernel.php");
	
	
//Verifica si esta habilitado el modo depuracion, para habilitar
	if(!isset($_SESSION['SQL_SHOW'])){
		$_SESSION['SQL_SHOW'] =  false;
	}
	
	if(isset($_GET["sql_show"])){
		switch ($_GET["sql_show"]) {
			case "ON":
				$_SESSION['SQL_SHOW'] =  true;
			break;
			
			default:
				$_SESSION['SQL_SHOW'] =  false;
		}
	}
	
	if(!isset($_SESSION["fullcontrols"])){
		$_SESSION["fullcontrols"] = false; 
	}

	if(isset($_GET["fullcontrols"])){
		
		switch ($_GET["fullcontrols"]) {
			case "ON":
				$_SESSION['fullcontrols'] =  true;
			break;
			
			default:
				$_SESSION['fullcontrols'] =  false;
		}
	}
	
	//Conecta con la Base de Datos
	if(!SimpleDAO::connect("localhost", "casita", "root", "")){
		echo $_SESSION['TAG']['bad_conection'];
	}
	
	//habilita registro
	SimpleDAO::$enableRecordLog = true;
	
	//Carga las etiquetas de idioma
	Handler::loadLang();

//toma la version

	$registrer='$HeadURL: $';
	$version = getVersion($registrer);
	define( 'APP_VERSION', $version);
?>