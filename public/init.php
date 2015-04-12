<?php
/**
*Create Date: 07/18/2011 22:44:25
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 161 $
*/

//Inicializa el trabajo con sesiones
	session_start();
	
//Rutas de la Aplicacion

	// LA RUTA DE LA APLICACION, APARTIR DEL ROOT DEL WEBSERVER
	//define( 'PATH_ROOT', "/base_project/" );
	define( 'PATH_ROOT', "/casita/public/" );
	//define( 'PATH_ROOT', "/casinos/" );
	
	
	// LA RUTA DE LA INFORMACION PRIVADA DE LA APLICACION, APARTIR DEL PATH_ROOTRR
	//define( 'PATH_PRIVATE', $_SERVER["DOCUMENT_ROOT"] . "/../base_project_private/" );
	//define( 'PATH_PRIVATE', "/home/binman/base_project_private/" );
	define( 'PATH_PRIVATE', "../private/" );
	
	// LA RUTA DE LA APLICACION, PARA ALMACENAR LOS LOGS
	define( 'PATH_LOGS', PATH_PRIVATE ."log/" );
	
	// LA RUTA DE LA APLICACION, PARA ALMACENAR LOS ARCHIVOS SUBIDOS
	//define( 'PATH_UPLOAD', "../../shopSite/public/images/" );
	define( 'PATH_UPLOAD', "images/" );
	
	define( 'PATH_FRAMEWORK', "framework/" );
	
	define( 'PATH_HANDLERS', "components/handlers/" );
	
	define( 'PATH_MODELS', "models/dao/" );
	

	//define( 'PATH_BUNDLE', "../../shopSite/private/" );
?>