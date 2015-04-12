<?php
/**
*Create Date: 07/18/2011 19:02:14
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 223 $
*/

	include("init.php");
	

	if(!isset($_SESSION['USER_ID']) || $_SESSION['USER_ID'] == ""){
		#header('Location: '.PATH_ROOT.'index.php');
		#exit();	
	}
	include(PATH_PRIVATE . 'config.php');
	include(PATH_PRIVATE . 'kernel.php');
?>