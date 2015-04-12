<?php
	/**
	 * 
	 */
	class Handler  {
		/**
		 * Almacena las variables que seran enviadas a las vistas
		 */
		private $_vars;
		
		private static $actionSufix = "Action";
		private static $handlerSufix = "Handler";
		
		const OUTPUT_FORMAT = "OUTPUT_FORMAT";
		const FORMAT_EXCEL = "EXCEL";
		
		//Almacena la accion que sera ejecutada
		public static $do;
	
		//almacena el nombre del script Actual
		public static $handler;
		
		protected $errors = array();
		
		public function getHandlerSufix(){
			return self::$handlerSufix;
		}
		
		public function haveErrors(){
			return (count($this->errors) > 0);
		}
		
		public function addError($msg){
			$this->errors[] = $msg;
		}
		
		public function addDbErrors($col, $errors){

			if(is_array($errors) && count($errors)>0){
				foreach ($errors as $key => $value) {
				
					if(!isset($col[$key])){
						$col[$key] = $key;
					}
					
					switch ($value) {
						case 'required':
							$msg = showMessage("field_required", array("field"=> $col[$key]));
						break;
						
						case 'too_long':
							$msg = showMessage("field_too_long", array("field"=> $col[$key]));
						break;
						
						case 'no_int':
							$msg = showMessage("field_no_int", array("field"=> $col[$key]));
						break;
						
						case 'no_decimal':
							$msg = showMessage("field_no_decimal", array("field"=> $col[$key]));
						break;
					}
						
					$this->addError($msg);
					
				}
			}
		}
		
		public function sendErrors($show = true){
			$json = array("errors"=>$this->errors);
			
			if($show){
				header('Cache-Control: no-cache, must-revalidate');
				header('Content-type: application/json');
				echo json_encode($json);
				exit;
			}
			
			return json_encode($json);
		}
		
		
		/**
		 * 
		 *Obtiene un attributo enviado a traves de el post o el get y le aplica trim, bd_escape, htmlentities
		 * @param $attr nombre del attributo
		 * @param $post true por defecto, false si se quiere buscar en GET
		 */
		public static function getRequestAttr($attr, $post = true){
			$attr = str_replace(".", "_", $attr);
			
			if($post){
				$var = $_POST;
			}else{
				$var = $_GET;
			}
			
		
			if(isset($var[$attr])){
				return trim($var[$attr]);
			}else{
				return null;
			}
		}
		
		/**
		 * 
		 *Asigna un attributo enviado a traves de el post o el get y le aplica trim, bd_escape, htmlentities
		 * @param $attr nombre del attributo
		 * @param $val valor
		 * @param $post true por defecto, false si se quiere buscar en GET
		 */
		public static function setRequestAttr($attr, $val, $post = true){
			$attr = str_replace(".", "_", $attr);
			
			if($post){
				$_POST[$attr] = trim($val);
			}else{
				$_GET[$attr] = trim($val);
			}
		}
		
		
		public function display($script, $args=array()){
			extract($args);
			include(PATH_PRIVATE . $script);
			
		}
		
		/**
		 * 
		 * Carga variable para ser accesada en las vistas
		 * @param unknown_type $key
		 * @param unknown_type $value
		 */
		public function setVar($key, $value){
			$this->_vars[$key] = $value;
		}
		
		/**
		 * 
		 * Obtiene variable registrada con setVar.
		 * retorna nulo si no existe
		 * @param unknown_type $key
		 */
		public function getVar($key){
			return (isset($this->_vars[$key]))? $this->_vars[$key] : null;
		}
		
		
		public function getAllVars(){
			return $this->_vars;
		}
		
		/**
		 * Genera cabezeras para imprimir en formato excel
		 * @$filename Es el nombre que tendra el archivo
		 */
		public function outputExcel($filename = "excel"){
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename='.$filename);
		}
		
		/**
		 * 
		 *Pone puntos suspensivos al final de cadenas cuya longitud sea mayor a $desde
		 * @param $str
		 * @param $desde
		 */
		public static function resumeDesde($str, $desde=25){
			if(strlen($str) > $desde){
				$str = substr($str, 0, $desde);
				$str.= "...";
			}
			return $str;
		}
		
		/**
		 * Envia a recrgar la pantalla por javascript
		 * @$script url de la nueva pagina
		 */
		public static function windowReload($script=false){
			echo "<script>";
			if($script){
				echo "window.location='$script'";
			}else{
				echo "location.reload(true)";
			}
			
			echo "</script>";
			exit;
		}
		
		/**
		 * 
		 * Genera el javascript nesesario para hacer una llamada asincronica
		 * @param $action: script que sera ejecutado. Se le agregara el PATH_ROOT
		 * @param $dest: contenedor DOM donse se insertara los datos
		 * @param $param: arreglo asosiativo de parametros que se enviaran al script con el metodo POST
		 * @param $noEcho: Si es true retorna un string solamente con la funcion de actualizacion, sin no lo imprime por echo.
		 */
		public static function asyncLoad($action, $dest, $param, $noEcho=false, $escape=true, $msg=""){
			
			
			//muestra el sql si se habilita el modo depuracion
			if($_SESSION['SQL_SHOW']){
				echo var_dump($param);
			}
			
			if($escape){
				$param = http_build_query($param, '', '&');
			}else{
				$p= "";
				foreach ($param as $key => $value) {
					$p .= "$key=$value&";
				}
				$param = substr($p, 0, -1);
			}
			
			$msg = addslashes($msg);
			
			if(trim($msg) == ""){
				$comand = "dom_update('$action', '$param', '$dest')";
			}else{
				$comand = "dom_confirm('$action', '$param', '$dest', '$msg')";
			}
			
			$action = PATH_ROOT . $action;
			
			if(!$noEcho){
				echo "<script>";
				echo $comand;
				echo "</script>";
			}else{
				return $comand;
			}
		}
		
		/**
		 * 
		 * Genera el javascript nesesario para hacer una llamada asincronica
		 * @param $action: script que sera ejecutado. Se le agregara el PATH_ROOT
		 * @param $dest: contenedor DOM donse se insertara los datos
		 * @param $param: arreglo asosiativo de parametros que se enviaran al script con el metodo POST
		 * @param $noEcho: Si es true retorna un string solamente con la funcion de actualizacion, sin no lo imprime por echo.
		 */
		public static function syncLoad($action, $dest, $param, $noEcho=false, $escape=true){
			
			
			//muestra el sql si se habilita el modo depuracion
			if($_SESSION['SQL_SHOW']){
				echo var_dump($param);
			}
			
			if($escape){
				$param = http_build_query($param, '', '&');
			}else{
				$p= "";
				foreach ($param as $key => $value) {
					$p .= "$key=$value&";
				}
				$param = substr($p, 0, -1);
			}
			
			
			$comand = "document.location='".PATH_ROOT."$action?$param'";
			
			
			$action = PATH_ROOT . $action;
			
			if(!$noEcho){
				echo "<script>";
				echo $comand;
				echo "</script>";
			}else{
				return $comand;
			}
		}
		
		/**
		 * 
		 * Genera el javascript nesesario para hacer una llamada asincronica
		 * @param $action: script que sera ejecutado. Se le agregara el PATH_ROOT
		 * @param $dest: contenedor DOM donse se insertara los datos
		 * @param $param: arreglo asosiativo de parametros que se enviaran al script con el metodo POST
		 * @param $noEcho: Si es true retorna un string solamente con la funcion de actualizacion, sin no lo imprime por echo.
		 */
		public static function asyncLoadInterval($action, $dest, $param, $noEcho=false, $escape=true, $interval=5){
			
			
			//muestra el sql si se habilita el modo depuracion
			if($_SESSION['SQL_SHOW']){
				echo var_dump($param);
			}
			
			if($escape){
				$param = http_build_query($param, '', '&');
			}else{
				$p= "";
				foreach ($param as $key => $value) {
					$p .= "$key=$value&";
				}
				$param = substr($p, 0, -1);
			}
			
			$comand = "dom_update_refresh('$action', '$param', '$dest', '$interval')";
			
			$action = PATH_ROOT . $action;
			
			if(!$noEcho){
				echo "<script>";
				echo $comand;
				echo "</script>";
			}else{
				return $comand;
			}
		}
		
		/**
		 * Carga el idioma
		 */
		private static function changeLang($lang)
		{
		
			if(!isset($_SESSION["LANG"]) || $_SESSION["LANG"] != $lang){
				$_SESSION["LANG"] = $lang;
				
				//ejecuta el query
				$sql = "SELECT `key`, " . $_SESSION["LANG"] . " FROM i18n";
				
				$sumary = SimpleDAO::execQuery($sql);
			
				 unset($_SESSION['TAG']);
				//carga los datos del query
				while($bdData = SimpleDAO::getNext($sumary) ){
					
					
					$_SESSION['TAG'][$bdData['key']] = $bdData[$_SESSION["LANG"]];
				}
			}
		}
		
		/**
		 * Recarga el idioma que se envivie por GET en la variable ln
		 */
		public static function loadLang()
		{
			if(isset($_GET["ln"])){
				$lang = Handler::getRequestAttr('ln',false);
				
				
				switch ($lang) {
					case "es":
					case "en":
						
					break;
					
					default:
						$lang = APP_LANG;
					break;
				}
				
				self::changeLang($lang);
			}else{
					
				if(!isset($_SESSION["LANG"])){
						
					self::changeLang(APP_LANG);
				}
			}
		}
	
		/**
		 * Crea un objeto del tipo exctamente igual al nombre del script ejecutado
		 * ejecuta el metodo con el nombre que se envie en la variable do
		 * la variable do se buscara en POSt y si no se encuentra, en GET
		 */
		public static function excec(){
			
			self::$do = self::getRequestAttr('do');
			if(!self::$do){
				self::$do = self::getRequestAttr('do',false);
			}
			
			 
			self::$handler = (isset($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
			self::$handler = explode("?", self::$handler);
			self::$handler = self::$handler[0];
			$partes_ruta = pathinfo(self::$handler);
			
			$className = $partes_ruta["filename"] . self::$handlerSufix;
			
			if(!class_exists($className))
				searchClass(PATH_HANDLERS, $className);
			
			if(!class_exists($className))
				searchClass(PATH_FRAMEWORK . PATH_HANDLERS, $className);
			
			if ($className != "Handler" && class_exists($className)) {
				self::$handler = $partes_ruta["filename"];

			    $mi_clase = new $className();
				
				if(method_exists($mi_clase, self::$do . self::$actionSufix)){
					$method = self::$do . self::$actionSufix;
					
					$mi_clase->$method();
				}else{
					$method = "index" . self::$actionSufix;
					
					if(method_exists($mi_clase, $method)){
						$mi_clase->$method();
					}
				}
				
				exit;
			}else{
				return false;
			}
		}
		
		/**
	 * Genera script para imprimir funcion js que genera la pgaginacion de una tabla
	 */
	public static function showPagination($name, $totalRows, $action, $param, $controls=null){
			
		$param = http_build_query($param, '', '&');
		$action = PATH_ROOT . $action;
		
		$show = array();
		
		if($controls){
			
			foreach($controls as $control){
				$show[$control]=true;
			}
		}
		$show = json_encode($show);
		
		echo "<script>";
		//showPagination(totalRows,dest,accion,params, maxPerPage)
		echo "showPagination($totalRows,'$name','$action','$param', '" . APP_DEFAULT_LIMIT_PER_PAGE . "', $show) ";
		echo "</script>";
		
	}
	
	/**
	 * Llena un prototipo con los valores q vienen de el post o get
	 * @param $prototype: arreglo con los datos a buscar
	 * @param $post: indica si buscara los valores en post o get
	 */
	public function fillPrototype($prototype , $post=true){
		
		
		foreach ($prototype as $key => $value) {
			$prototype[$key] = $this->toBdDate($this->getRequestAttr($key, $post));
		}
		

		return $prototype;
	}
	
	function genAttribs($data, $autoEcho = true){
		 	$msg = "";
		 	if(count($data)> 0){
				
				foreach ($data as $att => $val) {
						if($autoEcho){
							echo " $att = \"$val\" ";
						}
						else{
							$msg .= " $att = \"$val\" ";;
						}
					
				}
			}
			return $msg;
		}
	
	function toBdDate($strDate){
		$newDateString = $strDate;
		
		$parts = explode(' ', $strDate);
		
		if(count($parts) == 2){
			$strDate = $parts[0];
		}
		
		if(validDate($strDate)){
			switch (APP_DATE_FORMAT) {
				case 'DD-MM-YYYY':
					$format = "d-m-Y";
				break;
				
			}
			
			switch (DB_DATE_FORMAT) {
				case 'YYYY-MM-DD':
					$format_db = "Y-m-d";
				break;
				
			}
			
			if(count($parts) == 2){
				$format .= " g:i:sA";
				$format_db .= " G:i:s";
				
				$time = " " . $parts[1];
			}else{
				$time = "";
			}
			
			$myDateTime = DateTime::createFromFormat($format, $strDate . $time);
			$newDateString = $myDateTime->format($format_db);
		
		}	
		
		return $newDateString;
	}
	
	/**
	 * 
	 * Hace un snapshot de la llamada del script actual
	 * @param unknown_type $scriptKey
	 * @param unknown_type $showText
	 */
	public function registerAction($scriptKey, $showText){
		$total =count($_SESSION["HISTORY"]);
		for($i=0; $i < $total; $i++){
			if($_SESSION["HISTORY"][$i]["KEY"] == $scriptKey){
				break;
			}
		}
		
		//si encuentra ya ejecutada esa accion
		if($i < $total){
			
			//elimina las acciones posteriores
			for($j = $total; $j > $i; $j--){
				unset($_SESSION["HISTORY"][$j]);
			}
		}

		
		if($i == $total){
			$his = array();
			$his["KEY"]    = $scriptKey;
			$his["TEXT"]   = $showText;
			$his["TIME"]   = date("c");
			$his["GET"]    = http_build_query($_GET, '', '&amp;');
			$his["POST"]   = http_build_query($_POST, '', '&amp;');
			$his["ACTION"] = (isset($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
			$his["ACTION"] = explode("?", $his["ACTION"]);
			$his["ACTION"] = $his["ACTION"][0];
			/*
			 * self::$handler = (isset($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
			self::$handler = explode("?", self::$handler);
			self::$handler = self::$handler[0];
			 * */
			
			$_SESSION["HISTORY"][] = $his;
		}
		
	}
	
	public function clearSteps(){
		$_SESSION["HISTORY"] = array();
	}
	
	function historyBack($auto=false, $indexStep=1){
		$indexStep = intval($indexStep);
		$total = count($_SESSION["HISTORY"]);
	
		if($indexStep < $total){
			//eliminamos 1 para movernos por los indices del arreglo
			$total--;
			
			//si es 0 entonces regresa al inicio (indice 0)
			if($indexStep == 0){
				$indexStep = $total;
			}
			
			$action = $_SESSION["HISTORY"][$total - $indexStep]["ACTION"] . "?" . $_SESSION["HISTORY"][$total - $indexStep]["GET"];
			$post = $_SESSION["HISTORY"][$total - $indexStep]["POST"];
			
			if($auto){
				$script = "<script>";
				$script_end = "</script>";
			}else{
				$script = "";
				$script_end = "";
			}
			return $script . "dom_update('$action','$post','".APP_CONTENT_BODY."')" . $script_end;
		}else{
			return false;
		}
	}

	public static function reloadLast($auto=false){
		$total = count($_SESSION["HISTORY"]) - 1;

		if($total >= 0){

			$action = $_SESSION["HISTORY"][$total]["ACTION"] . "?" . html_entity_decode($_SESSION["HISTORY"][$total]["GET"]);
		
			$post = html_entity_decode($_SESSION["HISTORY"][$total]["POST"]);
			
			if($auto){
				$script = "<script>";
				$script_end = "</script>";
			}else{
				$script = "";
				$script_end = "";
			}
			$command =  $script . "dom_update('$action','$post','".APP_CONTENT_BODY."')" . $script_end;
			
			if($auto){
				echo $command;	
			}
			
			return $command;
		}else{
			return false;
		}
	}

	/**
	 * 
	 *Muestra $title en APP_CONTENT_TITLE
	 * @param  $title
	 */
	public function showTitle($title){
		echo "<script>";
		echo "$('".APP_CONTENT_TITLE."').update('<h1>$title</h1>');";
		echo "</script>";
	}
	
	/**
		 * 
		 * Genera el javascript nesesario para hacer una llamada asincronica
		 * @param $action: script que sera ejecutado. Se le agregara el PATH_ROOT
		 * @param $param: arreglo asosiativo de parametros que se enviaran al script con el metodo POST
		 * @param $noEcho: Si es true retorna un string solamente con la funcion de actualizacion, sin no lo imprime por echo.
		 */
		public static function makeURL($action, $param, $noEcho=false, $escape=true){
			
			
			//muestra el sql si se habilita el modo depuracion
			if($_SESSION['SQL_SHOW']){
				echo var_dump($param);
			}
			
			if($escape){
				$param = http_build_query($param, '', '&');
			}else{
				$p= "";
				foreach ($param as $key => $value) {
					$p .= "$key=$value&";
				}
				$param = substr($p, 0, -1);
			}
			
			
			$comand = PATH_ROOT."$action?$param";
			
			
			if(!$noEcho){
				echo $comand;
			}else{
				return $comand;
			}
		}

		public static function havePermission($permission){
			
			$check = in_array($permission, $_SESSION['USER_PERMISSIONS']);
				
			if(!$check){
				#para imprecion de mensajes de permiso faltante
				echo "#####################$permission";
			}
			
			return $check;
		}
}
	
?>