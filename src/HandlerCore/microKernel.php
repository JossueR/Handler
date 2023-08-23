<?php
/**
*Create Date: 07/18/2011 22:08:18
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 102 $
*Description: funciones de coneccion a la BD
*/

namespace HandlerCore;




    use DateTime;

    function validDate($strDate){
		$valid = false;

		if(Environment::$APP_DATE_FORMAT == "DD-MM-YYYY"){

			$date_pattern = "/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/";

			if (preg_match($date_pattern,$strDate))
			{
				$strDate = explode("-",$strDate );
				$valid = checkdate($strDate[1], $strDate[0], $strDate[2]);
			}

		}
		return $valid;
	}

	function get_include_contents($filename) {
	    if (is_file($filename)) {
	        ob_start();
	        include $filename;
	        return ob_get_clean();
	    }
	    return false;
	}

	/**
	 * Obtiene un texto a partir de la llave y reemplaza los valores los key en $data por su valor
	 */
	function showMessage($tagName, $data= array()){
		$pattern = "/\{([\w]+)\}/";
		$tagName = strtolower($tagName);
		if(isset($_SESSION['TAG'][$tagName])){
			$tag = $_SESSION['TAG'][$tagName];

			if(count($data) > 0)
			{
				preg_match_all($pattern, $tag, $matches, PREG_OFFSET_CAPTURE);

				for($i=0; $i < count($matches[0]); $i++){
					$foundKey = $matches[1][$i][0];

					if(!isset($data[$foundKey]) || $data[$foundKey] === null){
						$replaceWith = "";
					}else{
						$replaceWith = $data[$foundKey];
					}

					$tag = str_replace("{".$foundKey."}", $replaceWith, $tag);
				}
			}
			return $tag;

		}else{
			return "MISSING $tagName";
		}

	}

	/**
	 * Obtiene un texto a partir de la llave y reemplaza los valores los key en $data por su valor
	 */
	function buildMessage($message, $data= array()): array|string
    {
		$pattern = "/\{([\w]+)\}/";

		if(count($data) > 0)
		{
			preg_match_all($pattern, $message, $matches, PREG_OFFSET_CAPTURE);

			for($i=0; $i < count($matches[0]); $i++){
				$foundKey = $matches[1][$i][0];

				if(!isset($data[$foundKey])){
					$replaceWith = "";
				}else{
					$replaceWith = $data[$foundKey];
				}

				$message = str_replace("{".$foundKey."}", $replaceWith, $message);
			}
		}
		return $message;
	}

	function almacenMeses(){
		$baseTag = "mes_";
		$meses = array();

		for($i=1; $i<= 12; $i++){
			$x['id'] = $i;
			$x['desc'] = showMessage($baseTag . $i);

			$meses[] = $x;
		}

		return $meses;
	}

	/***
	 * Obtiene el ip desde el cual se está ingresando
	 */
	function getRealIpAddr()
	{
	    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
	    {
	      $ip=$_SERVER['HTTP_CLIENT_IP'];
	    }
	    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	    {
	      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    else
	    {
	      $ip=$_SERVER['REMOTE_ADDR'];
	    }
	    return $ip;
	}

	//convierte a numero decimal, elmina formatos con coma
	function parseValue($val){
		return floatval(str_replace(",", "",$val));
	}

	//include("class/ErrorLog.php");

	function searchClass($in, $className)
	{
		if ($handle = opendir(Environment::$PATH_PRIVATE . $in)) {

		    while (false !== ($entry = readdir($handle))) {

		        if (!is_dir(Environment::$PATH_PRIVATE . $in . $entry)) {
					loadClass($in . $entry, $className);
		        }else if($entry != "." && $entry != ".."){
		        	searchClass($in . $entry, $className);
		        }
		    }
		    closedir($handle);

		}
		return FALSE;
	}

	function loadClassIn($dir, $deep=false){
		if(is_file(Environment::$PATH_PRIVATE . $dir)){

			loadClass($dir);

		}else if ($handle = opendir(Environment::$PATH_PRIVATE . $dir)) {

		    while (false !== ($entry = readdir($handle))) {

		        if (!is_dir(Environment::$PATH_PRIVATE . $dir . $entry)) {
					loadClass($dir . $entry);
		        }else if($deep && $entry != "." && $entry != ".."){
		        	loadClassIn($dir . $entry, $deep);
		        }
		    }
		    closedir($handle);

		}
	}

	function loadClass($filename, $searchClassName=false){
		//echo var_dump($filename);
		$partes_ruta = pathinfo($filename);
		$className = $partes_ruta["filename"];

		if(class_exists($className)){
			//echo var_dump("Ya incluida");
			return true;
		}else{

			if(strpos($filename, '.php')=== false){
				$filename .= '.php';
			}

			if($searchClassName){
				//echo var_dump("----Buscando $searchClassName");
				if(strpos($searchClassName, '.php')=== false)
				{
					$searchClassName .= '.php';
				}
				$partes_ruta = pathinfo($filename);
				$className = $partes_ruta["basename"];
				if($searchClassName != $className){
					//echo var_dump("Nop");
					return false;
				}else{
					//echo var_dump("###Encontrada-> $searchClassName");
				}
			}

			if(is_file(Environment::$PATH_PRIVATE . $filename) ){
				//echo var_dump("cargando: " . PATH_PRIVATE . $filename);
				include Environment::$PATH_PRIVATE . $filename;

				return true;
			}else{
				//echo var_dump("no existe");
				return false;
			}
		}



	}


	function getVersion($svnPath): string
    {
		$version = "---";
		$svnPath = explode("/", $svnPath);

		for ($i=0; $i < count($svnPath); $i++) {
			if($svnPath[$i] == "branches" || $svnPath[$i] == "tags" ){
				$version = $svnPath[$i + 1];
				break;
			}
		}


		return $version;
	}


	function calcDateDiff( $date1, $date2): float|int|null
    {
        $data = null;

        try {
            $datetime1 = new DateTime($date1);
            $datetime2 = new DateTime($date2);

            $interval = $datetime1->diff($datetime2);

            $data = $interval->s;
            $data += $interval->i * 60;
            $data += $interval->h * 60 * 60;
        } catch (\Exception $e) {
        }

		return $data;
	}

	function truncateFloat($number, $digitos): string
    {
	    $raiz = 10;
	    $multiplicador = pow ($raiz,$digitos);
	    $resultado = ((int)($number * $multiplicador)) / $multiplicador;
	    return number_format($resultado, $digitos);

	}
