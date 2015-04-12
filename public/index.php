<?php
	include("init.php");
	
	include(PATH_PRIVATE . 'config.php');
	
   if(!Handler::excec()){
   	header("location:" . APP_DEFAULT_HANDLER);
   }
?>