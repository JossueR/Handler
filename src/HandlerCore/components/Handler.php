<?php
namespace HandlerCore\components;


use DateTime;
use HandlerCore\Environment;
use HandlerCore\models\dao\ConfigVarDAO;
use HandlerCore\models\SimpleDAO;
use JetBrains\PhpStorm\NoReturn;
use function HandlerCore\searchClass;
use function HandlerCore\showMessage;
use function HandlerCore\validDate;

/**
 * Clase base para el manejo de controladores y acciones en la aplicación.
 */
class Handler  {
    private $_vars;
    public static $SESSION;

    private static $actionSufix = "Action";
    private static $handlerSufix = "Handler";

    const OUTPUT_FORMAT = "OUTPUT_FORMAT";
    const FORMAT_EXCEL = "EXCEL";

    public static $do;

    public static $handler;

    protected $errors = array();

    private static $LAST_UNIC;

    private static $request_json;
    private static $mode_raw_request = false;
    protected bool $usePrivatePathInView = true;

    /**
     * Obtiene el sufijo que se utiliza para nombrar las clases de acción.
     * @return string Sufijo de acción.
     */
    public function getHandlerSufix(): string
    {
        return self::$handlerSufix;
    }

    /**
     * Obtiene el sufijo que se utiliza para nombrar los métodos de acción.
     * @return string Sufijo de acción.
     */
    public function getActionSufix(): string
    {
        return self::$actionSufix;
    }

    /**
     * Verifica si hay errores almacenados.
     * @return bool `true` si hay errores, de lo contrario `false`.
     */
    public function haveErrors(): bool
    {
        return (count($this->errors) > 0);
    }

    /**
     * Agrega un mensaje de error a la lista de errores.
     * @param string $msg Mensaje de error a agregar.
     * @return void
     */
    public function addError($msg): void
    {
        $this->errors[] = $msg;
    }

    /**
     * Obtiene todos los errores almacenados.
     * @return array Lista de errores.
     */
    public function getAllErrors(): array
    {
        return $this->errors;
    }

    /**
     * Agrega mensajes de error relacionados con la base de datos a la lista de errores.
     * @param array $col Columnas asociadas a los errores.
     * @param array $errors Errores relacionados con las columnas.
     * @return void
     */
    public function addDbErrors($col, $errors): void
    {

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

                    default:
                        $msg = $value;
                }

                $this->addError($msg);

            }
        }
    }

    /**
     * Envía los errores almacenados como una respuesta JSON.
     * @param bool $show `true` para mostrar la respuesta JSON, de lo contrario `false`.
     * @return string Respuesta JSON generada.
     */
    public function sendErrors($show = true): string
    {
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
     * Obtiene el valor de un atributo enviado a través de POST o GET y le aplica transformaciones.
     * @param string $attr Nombre del atributo.
     * @param bool $post `true` para buscar en POST, `false` para buscar en GET.
     * @return mixed Valor del atributo.
     */
    public static function getRequestAttr($attr, $post = true): mixed
    {

        //si no esta habilitado el modo Raw
        if(!self::$mode_raw_request){
            $attr = str_replace(".", "_", $attr);

            if($post){
                $var = $_POST;
            }else{
                $var = $_GET;
            }
        }else{
            //modo raw busca la data en el objeto ya serializado
            $var = self::$request_json;
        }



        if(isset($var[$attr])){


            return self::trim_r($var[$attr]);

        }else{
            if($post){
                return self::getRequestAttr($attr, false);
            }else{
                return null;
            }

        }
    }

    /**
     * Asigna un valor a un atributo como si fuera enviado a través de POST o GET.
     * @param string $attr Nombre del atributo.
     * @param mixed $val Valor a asignar.
     * @param bool $post `true` para enviar en POST, `false` para enviar en GET.
     * @return void
     */
    public static function setRequestAttr($attr, $val, $post = true): void
    {
        $attr = str_replace(".", "_", $attr);

        if(!is_array($val)){
            $val = trim($val);
        }

        //si no esta habilitado el modo Raw
        if(!self::$mode_raw_request){
            if($post){
                $_POST[$attr] = $val;
            }else{
                $_GET[$attr] = $val;
            }
        }else{
            self::$request_json[$attr] = $val;
        }
    }


    /**
     * Muestra contenido en una vista.
     * @param string $script Ruta al script de vista.
     * @param array $args Argumentos a pasar a la vista.
     * @param bool $autoshoy `true` para mostrar automáticamente, `false` para retornar como cadena.
     * @return string Contenido de la vista o cadena vacía si no se muestra automáticamente.
     */
    public function display($script, $args=array(), $autoshoy=true){
        extract($args);

        if(!$autoshoy){
            ob_start();
        }
        $path_prefix = ($this->usePrivatePathInView)? Environment::$PATH_PRIVATE : "";
        include($path_prefix . $script);

        if(!$autoshoy){
            return ob_get_clean();
        }
    }

    /**
     * Establece un valor para ser utilizado en las vistas.
     * @param string $key Clave del valor.
     * @param mixed $value Valor a establecer.
     * @return void
     */
    public function setVar($key, $value): void
    {
        $this->_vars[$key] = $value;
    }

    /**
     * Obtiene un valor registrado previamente
     * @param string $key Clave del valor.
     * @return mixed Valor almacenado o `null` si no existe.
     */
    public function getVar($key): mixed
    {
        return (isset($this->_vars[$key]))? $this->_vars[$key] : null;
    }


    /**
     * Obtiene todos los valores registrados previamente
     * @return array|null Valores registrados en las vistas.
     */
    public function getAllVars(): ?array
    {
        return $this->_vars;
    }

    /**
     * Establece un conjunto completo de valores para las vistas.
     * @param array $all Valores a establecer.
     * @return void
     */
    public function setALLVars($all){
        $this->_vars = $all;
    }

    /**
     * Genera encabezados para la impresión en formato Excel.
     * @param string $filename Nombre del archivo Excel.
     * @param bool $html `true` si se incluirá encabezado HTML, `false` en caso contrario.
     * @return void
     */
    public function outputExcel($filename = "excel.xls", $html = true): void
    {
        header ('Content-Type: application/vnd.ms-excel');

        header ('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename='.$filename );

        if($html){
            echo '<html><head><meta http-equiv="content-type" content="application/vnd.ms-excel;" charset="UTF-8">
						<meta charset="UTF-8"></head>';
        }
    }

    /**
     * Genera una versión truncada de una cadena.
     * @param string $str Cadena original.
     * @param int $desde Longitud límite.
     * @return string Cadena truncada si es necesario.
     */
    public static function resumeDesde($str, $desde=25){
        if(strlen($str) > $desde){
            $str = substr($str, 0, $desde);
            $str.= "...";
        }
        return $str;
    }

    /**
     * Recarga la página actual mediante JavaScript.
     * @param string|bool $script URL de la nueva página o `false` para recargar la página actual.
     * @return void
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
     * Genera el JavaScript necesario para realizar una carga asincrónica.
     * @param string $action Script a ejecutar.
     * @param string $dest Contenedor DOM donde se mostrará la respuesta.
     * @param array $param Datos que se enviaran
     * @param bool $noEcho Índica si se imprime automáticamente
     * @param bool $escape Índica si se aplica escape a los caracteres automáticamente
     * @return string Código JavaScript para la carga asincrónica.
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

        $action = Environment::$PATH_ROOT . $action;

        if(!$noEcho){
            echo "<script>";
            echo $comand;
            echo "</script>";
        }else{
            return $comand;
        }
    }



    /**
     * Genera el JavaScript necesario para realizar una carga sincrónica.
     * @param string $action Script a ejecutar.
     * @param string $dest Contenedor DOM donde se mostrará la respuesta.
     * @param array $param Datos que se enviaran
     * @param bool $noEcho Índica si se imprime automáticamente
     * @param bool $escape Índica si se aplica escape a los caracteres automáticamente
     * @return string Código JavaScript para la carga asincrónica.
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

        if($dest==""){
            $comand = "window.location.href='".Environment::$PATH_ROOT."$action?$param'";
        }else{
            $comand = "window.open('".Environment::$PATH_ROOT."$action?$param')";
        }



        $action = Environment::$PATH_ROOT . $action;

        if(!$noEcho){
            echo "<script>";
            echo $comand;
            echo "</script>";
        }else{
            return $comand;
        }
    }


    public static function goAnchor($anchor, $autoshow=false){


        //muestra el sql si se habilita el modo depuracion
        if($_SESSION['SQL_SHOW']){
            echo var_dump($anchor);
        }



        $comand = "location.hash = '#$anchor'";

        if($autoshow){
            echo "<script>$comand</script>";
        }


        return $comand;

    }



    /**
     * Carga el idioma y almacena las traducciones en la sesión.
     * @param string $lang Idioma a cargar.
     * @param bool $force Indica si se debe forzar la carga del idioma incluso si ya está cargado.
     * @param bool $use_session Indica si se debe usar la sesión para almacenar el idioma.
     * @return void
     */
    private static function changeLang($lang, $force=false, $use_session=true)
    {
        self::$SESSION["LANG"] = $lang;
        SimpleDAO::setDataVar("LANG", $lang);
        if(!isset($_SESSION["LANG"]) || $_SESSION["LANG"] != $lang || $force){
            $_SESSION["LANG"] = $lang;

            //ejecuta el query
            $sql = "SELECT `key`, " . $lang . " FROM i18n";

            $sumary = SimpleDAO::execQuery($sql);

            unset($_SESSION['TAG']);
            //carga los datos del query
            while($bdData = SimpleDAO::getNext($sumary) ){

                self::$SESSION['TAG'][strtolower($bdData['key'])] = $bdData[$lang];
                $_SESSION['TAG'][strtolower($bdData['key'])] = $bdData[$lang];
            }
        }
    }

    /**
     * Recarga el idioma basado en el valor proporcionado en el parámetro GET 'ln'.
     * @param bool $force Indica si se debe forzar la recarga del idioma incluso si ya está cargado.
     * @param bool $use_session Indica si se debe usar la sesión para almacenar el idioma.
     * @return void
     */
    public static function loadLang($force = false, $use_session=true)
    {
        if(isset($_GET["ln"])){
            $lang = Handler::getRequestAttr('ln',false);


            switch ($lang) {
                case "es":
                case "en":

                    break;

                default:
                    $lang = Environment::$APP_LANG;
                    break;
            }

            self::changeLang($lang);
        }else{

            if(!isset($_SESSION["LANG"]) ){

                $lang = Environment::$APP_LANG;
            }else{
                $lang = $_SESSION["LANG"];
            }

            if(!isset($_SESSION["LANG"]) || $force){

                self::changeLang($lang, $force, $use_session);
            }
        }
    }

    /**
     * Obtiene el nombre del controlador solicitado en la URL.
     * @return string Nombre del controlador solicitado.
     */
    public static function getRequestedHandlerName(){
        $h = (isset($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
        $h = explode("?", $h);
        $h = $h[0];
        $partes_ruta = pathinfo($h);

        return $partes_ruta["filename"];
    }


    /**
     * Ejecuta el controlador correspondiente según la solicitud y realiza las acciones necesarias.
     * @return void|bool Si se ejecuta un controlador válido, se ejecutan las acciones correspondientes y se termina el script. Si no se encuentra un controlador válido, devuelve false.
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

        if(!class_exists($className)){
            $className = Environment:: $NAMESPACE_HANDLERS .$className;
        }

        if ($className != "Handler" && class_exists($className)) {
            self::$handler = $partes_ruta["filename"];

            $mi_clase = new $className();


            if(!($mi_clase instanceof ResponseHandler)){
                if(session_status() == PHP_SESSION_NONE){
                    session_start();
                }
                $use_session=true;
            }else{
                $use_session=false;
            }

            self::configSession($use_session);

            //si no es el login
            if(!($mi_clase instanceof UnsecureHandler) &&
                !($mi_clase instanceof ResponseHandler)
            ){

                if(!isset(self::$SESSION['USER_ID']) || self::$SESSION['USER_ID'] == "" || !DynamicSecurityAccess::havePermission(Environment::$ACCESS_PERMISSION) ){
                    self::windowReload(Environment::$ACCESS_HANDLER);
                }

                SimpleDAO::setDataVar("USER_NAME", self::$SESSION['USER_NAME']);
            }
            $mi_clase->init();
            if(method_exists($mi_clase, self::$do . self::$actionSufix)){
                $method = self::$do . self::$actionSufix;

                $sec = new DynamicSecurityAccess();
                if($sec->checkHandlerActionAccess($className, self::$do . self::$actionSufix)){
                    $mi_clase->$method();
                }else{
                    echo "no permiso: " . $sec->getFailPermission();
                }

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
     * Configura los datos de session en el controlador.
     * @param $use_session
     * @return void
     */
    private static function configSession($use_session=true){
        self::$SESSION['USER_ID'] = "";
        self::$SESSION['USER_NAME'] ="";

        if(isset($_SESSION['USER_ID'])){
            self::$SESSION['USER_ID'] = $_SESSION['USER_ID'];
        }

        if(isset($_SESSION['USER_NAME'])){
            self::$SESSION['USER_NAME'] = $_SESSION['USER_NAME'];
        }

        if(isset($_SESSION['show_name'])){
            DynamicSecurityAccess::$show_names = $_SESSION['show_name'];
        }


        if($use_session){
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

            //Verifica si esta habilitado el modo depuracion, para habilitar
            if(!isset($_SESSION['SQL_SHOW'])){
                $_SESSION['SQL_SHOW'] =  false;
                SimpleDAO::setDataVar("SQL_SHOW", false);
            }else{
                SimpleDAO::setDataVar("SQL_SHOW", $_SESSION['SQL_SHOW']);
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

            if(isset($_SESSION['LANG'])){
                SimpleDAO::setDataVar("LANG", $_SESSION['LANG']);
            }
        }


        if(isset($_GET["sql_show"])){
            switch ($_GET["sql_show"]) {
                case "ON":
                    $_SESSION['SQL_SHOW'] =  true;
                    SimpleDAO::setDataVar("SQL_SHOW", true);
                    break;

                default:
                    $_SESSION['SQL_SHOW'] =  false;
                    SimpleDAO::setDataVar("SQL_SHOW", false);
            }
        }

        if(isset($_GET["show_name"])){
            switch ($_GET["show_name"]) {
                case "ON":
                    $_SESSION['show_name'] =  true;

                    break;

                default:
                    $_SESSION['show_name'] =  false;

            }

            DynamicSecurityAccess::$show_names = $_SESSION['show_name'];
        }

        //Carga las etiquetas de idioma
        self::loadLang(false, $use_session);
    }

    /**
     * Genera el script necesario para imprimir una función JavaScript que permite crear la paginación de una tabla.
     *
     * Esta función genera el código JavaScript necesario para mostrar una paginación en una tabla HTML. Permite navegar entre las páginas de los resultados.
     *
     * @param string $name Nombre único para la instancia de la paginación (usado en el identificador HTML).
     * @param int $totalRows El total de filas o elementos a paginar.
     * @param string $action La acción o URL a la que se enviarán las solicitudes de paginación.
     * @param string $param El parámetro que se agregará a las solicitudes de paginación para indicar la página seleccionada.
     * @param array|null $controls Un arreglo opcional de configuraciones para los controles de la paginación (anterior, siguiente, etc.).
     *                            Ejemplo: ['prevLabel' => 'Anterior', 'nextLabel' => 'Siguiente'].
     *
     * @return void Genera el código JavaScript para crear la paginación en el lugar donde se coloque este script.
     */
    public static function showPagination($name, $totalRows, $action, $param, $controls=null){

        $param = http_build_query($param, '', '&');
        $action = Environment::$PATH_ROOT . $action;

        $show = array();

        if($controls){

            foreach($controls as $control){
                $show[$control]=true;
            }
        }
        $show = json_encode($show);

        echo "<script>";
        //showPagination(totalRows,dest,accion,params, maxPerPage)
        echo "showPagination($totalRows,'$name','$action','$param', '" . Environment::$APP_DEFAULT_LIMIT_PER_PAGE . "', $show) ";
        echo "</script>";

    }

    /**
     * Llena un prototipo con los valores provenientes de las variables POST o GET.
     *
     * Este método se encarga de llenar un prototipo (arreglo asociativo) con los valores obtenidos de las variables POST o GET, correspondientes a las claves proporcionadas en el arreglo "$prototype". También permite establecer valores predeterminados en el prototipo en caso de que no se encuentren los valores en las variables de solicitud.
     *
     * @param array $prototype Un arreglo asociativo con las claves (nombres de variables) y sus valores predeterminados a buscar.
     * @param bool $post Indica si se deben buscar los valores en las variables POST (true) o GET (false).
     * @return array El prototipo llenado con los valores obtenidos de las variables de solicitud y aplicando los valores predeterminados cuando sea necesario.
     */
    public function fillPrototype($prototype , $post=true): array
    {


        foreach ($prototype as $key => $default_value) {
            $prototype[$key] = $this->toBdDate($this->getRequestAttr($key, $post));

            if(is_null($prototype[$key]) && $default_value != null){
                $prototype[$key] = $default_value;
            }
        }


        return $prototype;
    }

    /**
     * Genera una cadena de atributos HTML a partir de un arreglo asociativo de datos.
     *
     * Este método toma un arreglo asociativo de datos que representan atributos y valores para un elemento HTML y genera una cadena de texto formateada con los atributos y sus valores correspondientes en el formato aceptado por las etiquetas HTML.
     *
     * @param array $data Un arreglo asociativo que contiene los nombres de los atributos como claves y sus respectivos valores.
     * @param bool $autoEcho Indica si los atributos deben imprimirse directamente o ser devueltos como cadena.
     * @return string Si "$autoEcho" es false, devuelve una cadena con los atributos HTML formateados. Si es true, imprime los atributos directamente y devuelve una cadena vacía.
     */
    static function genAttribs($data, $autoEcho = true){
        $msg = "";
        if($data != null && count($data)> 0){

            foreach ($data as $att => $val) {
                if(is_array($val)){
                    $val = "'" . json_encode($val). "'";
                }else{
                    $val = "\"$val\"";
                }

                if($autoEcho){
                    echo " $att = $val ";
                }
                else{
                    $msg .= " $att = $val ";;
                }

            }
        }
        return $msg;
    }

    /**
     * Convierte una fecha en formato legible por humanos al formato de fecha utilizado en la base de datos.
     *
     * Este método toma una fecha en formato legible por humanos y la convierte al formato de fecha utilizado en la base de datos. La función tiene en cuenta los formatos de fecha y hora definidos en el entorno de la aplicación.
     *
     * @param string $strDate La fecha en formato legible por humanos que se desea convertir.
     * @return string|null La fecha convertida al formato utilizado en la base de datos.
     */
    function toBdDate($strDate): ?string
    {
        $newDateString = $strDate;

        if(!is_array($newDateString)) {
            $parts = explode(' ', $strDate);

            if (count($parts) == 2) {
                $strDate = $parts[0];
            }

            if (validDate($strDate)) {
                if (Environment::$APP_DATE_FORMAT == 'DD-MM-YYYY') {
                    $format = "d-m-Y";
                }

                if (Environment::$DB_DATE_FORMAT == 'YYYY-MM-DD') {
                    $format_db = "Y-m-d";
                }

                if (count($parts) == 2) {
                    $format .= " g:i:sA";
                    $format_db .= " G:i:s";

                    $time = " " . $parts[1];
                } else {
                    $time = "";
                }

                $myDateTime = DateTime::createFromFormat($format, $strDate . $time);
                $newDateString = $myDateTime->format($format_db);

            }
        }

        return $newDateString;
    }


    /**
     * Registra una acción realizada en el controlador para generar un historial de acciones.
     *
     * Este método registra una acción realizada en el controlador en el historial de acciones de la sesión actual. Si la acción ya ha sido registrada previamente, elimina las acciones posteriores para mantener la coherencia en el historial.
     *
     * @param string $scriptKey La clave que identifica la acción realizada.
     * @param string $showText El texto descriptivo de la acción realizada.
     * @return void
     */
    public function registerAction($scriptKey, $showText){
        $total =count($_SESSION["HISTORY"]);
        for($i=0; $i < $total; $i++){
            if($_SESSION["HISTORY"][$i]["KEY"] == $scriptKey){
                break;
            }
        }

        //si encuentra ya ejecutada esa acción
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


            $_SESSION["HISTORY"][] = $his;
        }

    }

    /**
     * Limpia el historial de acciones registradas en la sesión actual.
     *
     * Este método permite limpiar el historial de acciones registradas en la sesión actual. Se pueden eliminar todas las acciones registradas o un número específico de pasos hacia atrás en el historial.
     *
     * @param int $steps El número de pasos hacia atrás en el historial que se desea eliminar. Si se establece en 0 (valor predeterminado), se eliminarán todas las acciones registradas en el historial.
     * @return void
     */
    public function clearSteps($steps=0){

        if($steps == 0){
            $_SESSION["HISTORY"] = array();
        }else{
            if(is_array($_SESSION["HISTORY"]) &&  count($_SESSION["HISTORY"]) >= $steps){

                for ($i=0; $i < $steps; $i++){
                    array_pop($_SESSION["HISTORY"]);
                }
            }

        }

    }

    /**
     * Realiza un redireccionamiento a una acción previamente registrada en el historial.
     *
     * Este método permite redirigir a una acción previamente registrada en el historial de acciones. El índice del paso hacia atrás se puede especificar para determinar qué acción se debe tomar en función de la posición en el historial.
     *
     * @param bool $auto Indica si se debe generar automáticamente el script de redireccionamiento en JavaScript. Por defecto, es falso.
     * @param int $indexStep El índice del paso hacia atrás en el historial que se debe utilizar para el redireccionamiento. Por defecto, es 1 (el paso anterior).
     * @return string|bool Devuelve el script de redireccionamiento en JavaScript si $auto es verdadero y hay acciones en el historial, de lo contrario, devuelve falso.
     */
    function historyBack($auto=false, $indexStep=1){
        $indexStep = intval($indexStep);
        $total = count($_SESSION["HISTORY"]);

        if($indexStep < $total){
            //eliminamos 1 para movernos por los índices del arreglo
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
            return $script . "dom_update('$action','$post','".Environment::$APP_CONTENT_BODY."')" . $script_end;
        }else{
            return false;
        }
    }

    /**
     * Recarga la última acción registrada en el historial.
     *
     * Este método permite recargar la última acción registrada en el historial de acciones. Se puede controlar si se genera automáticamente el script de recarga en JavaScript y si se muestra directamente en la página.
     *
     * @param bool $auto Indica si se debe generar automáticamente y mostrar el script de recarga en JavaScript. Por defecto, es falso.
     * @return string|bool Devuelve el script de recarga en JavaScript si $auto es verdadero y hay acciones en el historial, de lo contrario, devuelve falso.
     */
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
            $command =  $script . "dom_update('$action','$post','".Environment::$APP_CONTENT_BODY."')" . $script_end;

            if($auto){
                echo $command;
            }

            return $command;
        }else{
            return false;
        }
    }

    /**
     * Actualiza el título del contenido en la página mediante JavaScript.
     *
     * Este método permite actualizar dinámicamente el título del contenido en la página web utilizando JavaScript. Se proporciona el título que se desea mostrar y se genera un script que actualiza el elemento HTML correspondiente.
     *
     * @param string $title El título que se desea mostrar en el contenido.
     * @return void
     */
    public function showTitle($title){
        echo "<script>";
        echo "$('#".Environment::$APP_CONTENT_TITLE."').html('$title');";
        echo "</script>";
    }

    /**
     * Genera el código JavaScript necesario para realizar una llamada asincrónica.
     *
     * Este método genera el código JavaScript necesario para realizar una llamada asincrónica a un script especificado. Se agrega el PATH_ROOT al script y se proporciona un arreglo asociativo de parámetros que se enviarán al script mediante el método POST. Opcionalmente, se puede indicar si se desea obtener el código JavaScript como una cadena de texto sin imprimirlo directamente mediante echo.
     *
     * @param string $action El nombre del script que se ejecutará, al que se agregará el PATH_ROOT.
     * @param array $param Un arreglo asociativo de parámetros que se enviarán al script mediante POST.
     * @param bool $noEcho Si es true, devuelve el código JavaScript en una cadena en lugar de imprimirlo con echo.
     * @param bool $escape Si es true, se aplicará la función http_build_query para escapar los parámetros. Si es false, se construirá manualmente la cadena de parámetros.
     * @return void|string Si $noEcho es true, devuelve el código JavaScript generado como una cadena. De lo contrario, no devuelve nada.
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


        $comand = Environment::$PATH_ROOT."$action?$param";


        if(!$noEcho){
            echo $comand;
        }else{
            return $comand;
        }
    }

    /**
     * Verifica si el usuario actual tiene el permiso solicitado.
     *
     * Este método verifica si el usuario actual tiene el permiso especificado. Utiliza el método havePermission de la clase DynamicSecurityAccess para realizar la verificación.
     *
     * @param string $permission El nombre del permiso que se desea verificar.
     * @return bool Retorna true si el usuario tiene el permiso, y false si no lo tiene.
     */
    public static function havePermission($permission): bool
    {
        return DynamicSecurityAccess::havePermission($permission);
    }

    public static function loadValue($field, $val, $noEcho = true){

        $comand = "loadAndShow('$field','$val')";


        if(!$noEcho){
            echo "<script>";
            echo $comand;
            echo "</script>";
        }else{
            return $comand;
        }
    }

    /**
     * Obtiene el nombre de usuario actual.
     *
     * Este método devuelve el nombre de usuario del usuario actual almacenado en la sesión.
     *
     * @return string El nombre de usuario del usuario actual.
     */
    public static function getUsename(){

        return self::$SESSION["USER_NAME"];
    }

    /**
     * Obtiene el nombre completo del usuario actual.
     *
     * Este método devuelve el nombre completo del usuario actual almacenado en la sesión.
     *
     * @return string El nombre completo del usuario actual.
     */
    public static function getUseFullname(){
        return $_SESSION["usuario_nombre"];
    }

    /**
     * Genera un nombre único.
     *
     * Este método genera y devuelve un nombre único utilizando la marca de tiempo actual con microsegundos.
     *
     * @return string El nombre único generado.
     */
    public function getUnicName(): string
    {

        do{
            $sid = microtime(true);
            $sid = str_replace(".", "", $sid);
        }while ($sid == self::$LAST_UNIC);




        self::$LAST_UNIC = $sid;

        return $sid;
    }

    /**
     * Envía una respuesta en formato JSON.
     *
     * Este método envía una respuesta en formato JSON al cliente. Puede incluir encabezados
     * de respuesta para especificar el tipo de contenido y control de caché.
     *
     * @param mixed $data Los datos que se convertirán a JSON y se enviarán como respuesta.
     * @param bool $header Determina si se deben agregar los encabezados de respuesta adecuados.
     * @param bool $show Determina si se debe mostrar la respuesta JSON en la salida.
     *
     * @return string La representación JSON de los datos enviados.
     */
    public static function sendJSON($data, $header = true, $show= true){

        if($header){
            header('Cache-Control: no-cache, must-revalidate');
            header('Content-type: application/json');
        }



        $json = json_encode($data);

        if($show){
            echo $json;
        }
        return $json;
    }

    /**
     * Obtiene el nombre del controlador actual.
     *
     * Este método devuelve el nombre del controlador actual, eliminando el sufijo
     * que pueda estar presente. Se utiliza para determinar el nombre del controlador que está
     * siendo ejecutado.
     *
     * @return string El nombre del controlador actual.
     */
    public function getHandlerName(): string
    {
        $n =	get_class($this);
        $n = basename($n);

        $i = strpos($n, $this->getHandlerSufix());

        if($i !== false){
            $n = substr($n, 0, $i);
        }

        return $n;
    }

    /**
     * Obtiene el valor de configuración que indica si se debe verificar los permisos de acceso.
     *
     * Este método devuelve el valor de configuración que determina si se debe realizar una verificación
     * de permisos de acceso antes de ejecutar ciertas acciones. Puede ser utilizado para determinar si
     * la aplicación está configurada para realizar comprobaciones de permisos antes de permitir el acceso
     * a determinadas funciones.
     *
     * @return bool Valor de configuración para la comprobación de permisos.
     */
    public static function getPermissionCheck(){
        return $_SESSION["CONF"][ConfigVarDAO::VAR_PERMISSION_CHECK];
    }

    /**
     * Obtiene el idioma actual utilizado en la aplicación.
     *
     * Este método devuelve el idioma actual que está siendo utilizado en la aplicación. Puede ser útil
     * para mostrar contenido en el idioma adecuado o para realizar operaciones específicas basadas en el idioma.
     *
     * @return string Código del idioma actual.
     */
    public static function getLang(): string
    {
        return $_SESSION["LANG"];
    }

    /**
     * Recorre recursivamente un arreglo y elimina los espacios en blanco de cada valor.
     *
     * Este método toma un arreglo y, de manera recursiva, elimina los espacios en blanco de cada uno de los
     * valores. Si se proporciona un valor que no es un arreglo, simplemente se le quitarán los espacios en blanco.
     * Si el valor es un arreglo, se aplicará la misma función a cada uno de sus elementos.
     *
     * @param mixed $arr El arreglo (o valor) del cual se eliminarán los espacios en blanco.
     * @return mixed El arreglo modificado con los espacios en blanco eliminados, o el valor con los espacios en blanco eliminados.
     */
    public static function trim_r($arr)
    {
        return is_array($arr) ? array_map('self::trim_r', $arr) : trim($arr);
    }

    /**
     * Genera una etiqueta de enlace HTML con atributos opcionales y texto dado.
     *
     * Este método crea una etiqueta de enlace HTML <a> con el texto proporcionado y el enlace especificado.
     * Se pueden incluir atributos HTML adicionales usando el parámetro $html_params.
     * Si se proporciona un valor en $href, se utilizará como el atributo "href" del enlace.
     * Si no se proporciona un valor en $href, se generará un atributo "onclick" en su lugar.
     *
     * @param string $text El texto visible del enlace.
     * @param string $link El enlace al que apunta el enlace.
     * @param bool $show Indica si mostrar el enlace inmediatamente (true) o solo generar el HTML (false).
     * @param string|null $href El valor del atributo "href" del enlace. Si se omite, se generará un atributo "onclick" en su lugar.
     * @param array|null $html_params Un arreglo asociativo de atributos HTML adicionales para la etiqueta <a>.
     * @return string El HTML de la etiqueta de enlace generada.
     */
    public static function make_link($text, $link, $show = true, $href=null, $html_params = null){
        $onclick = "";

        if(!$href){

            $onclick = 'onclick="'.$link.'"';
            $href = 'javascript: void(0)';
        }

        $attrs = self::genAttribs($html_params, false);
        $link = "<a href='$href' $onclick $attrs >$text</a>";

        if($show){
            echo $link;
        }
        return $link;
    }

    /**
     * Habilita la obtención de datos de solicitud en formato crudo (raw) y los almacena en la clase.
     *
     * Este método permite obtener datos de solicitud en formato crudo y decodificarlos como JSON si es posible.
     * Si la decodificación de JSON no tiene éxito, se usan los datos de solicitud normales ($_REQUEST).
     * Los datos obtenidos se almacenan en la clase para su posterior acceso utilizando el método getRequestAttribute.
     *
     * @return void
     */
    public static function enableRawRequest(): void
    {
        $raw = file_get_contents('php://input');
        self::$request_json = json_decode($raw,true);

        //si no puede decodificarlo
        if(!self::$request_json){
            //usa el request
            self::$request_json = $_REQUEST;
        }

        self::$mode_raw_request = true;
    }

    /**
     * Verifica si está habilitada la obtención de datos de solicitud en formato crudo (raw).
     *
     * Este método verifica si se ha habilitado el modo de obtención de datos de solicitud en formato crudo.
     * Devuelve un valor booleano que indica si la obtención en formato crudo está activa o no.
     *
     * @return bool True si la obtención en formato crudo está habilitada, False en caso contrario.
     */
    public static function isRawEnabled(): bool
    {
        return self::$mode_raw_request;
    }

    /**
     * Obtiene todos los datos de la solicitud.
     *
     * Este método recupera todos los datos de la solicitud, ya sea en formato crudo o a través de los arrays
     * superglobales $_POST y $_GET, dependiendo de si el modo de obtención en formato crudo está habilitado o no.
     * Puede especificarse si se deben recuperar los datos de POST o GET a través del parámetro $post.
     *
     * @param bool $post Determina si se deben recuperar los datos de POST (True) o GET (False). Por defecto, es True.
     *
     * @return array Un arreglo que contiene todos los datos de la solicitud, ya sean de POST, GET o en formato crudo.
     */
    public static function getAllRequestData($post = true): array
    {
        $data = array();

        if(self::isRawEnabled()){
            $data = self::$request_json;
        }else{
            if($post){
                $data = $_POST;
            }else{
                $data = $_GET;
            }
        }

        return $data;
    }

    /**
     * @return void
     * Se llama antes de ejecutar el action
     */
    protected function init(){

    }
}
