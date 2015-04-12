<?php
/**
*Create Date: 07/18/2011 23:01:28
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 234 $
*/
	//el kernel solo puede ser cargado cuando el usuario esta logeado
	if(!isset($_SESSION['USER_ID']) || $_SESSION['USER_ID'] == ""){
		//redirecciona
		Handler::windowReload("login");
	}
	
	if(! isset($GLOBALS['kernel'])){
		$GLOBALS['kernel'] = true;
		
		
		
		function validDate($strDate){
			$valid = false;
			
			if(APP_DATE_FORMAT == "DD-MM-YYYY"){
		
				$date_pattern = "/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/";
				
				if (preg_match($date_pattern,$strDate))
				{
					$strDate = explode("-",$strDate );
					$valid = checkdate($strDate[1], $strDate[0], $strDate[2]);
				}
	
			}
			return $valid;
		}
		
		
		
		
		function sendError($tagName, $autoExit=true){
			$errors = array();
			$errors['errors'][] = showMessage($tagName);
			
			echo(json_encode($errors));
			
			if($autoExit){
				header("HTTP/1.1 401 Authorization Required");
				header('Status: 401 Authorization Required');
				
				
				exit();	
			}
			
		}
		
		loadClassIn(PATH_FRAMEWORK .'models/dao/');
		loadClassIn(PATH_FRAMEWORK .'components/handlers/');
		
	}

	
	
?>