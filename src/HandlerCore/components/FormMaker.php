<?php
namespace HandlerCore\components;

    use HandlerCore\Environment;
    use HandlerCore\models\dao\AbstractBaseDAO;
    use HandlerCore\models\dao\FormFieldConfigDAO;
    use HandlerCore\models\SimpleDAO;
    use function HandlerCore\showMessage;

    /**
     * Clase FormMaker que genera formularios HTML basados en configuraciones.
     */
    class FormMaker extends Handler implements ShowableInterface{

        public $name;
		public $action;
		public $actionDO;
		public $prototype;
		public $legents;
		public $sources;
		public $types;
		public $resultID;
		public $params;
		public $searchAction;
		public $searchParams;
		public $showAction;
		public $showParams;
        /**
         * @var string|null Ruta de la plantilla para el formulario.
         */
		private $schema;
		public $prefix;
		public $sufix;
		public $encType = false;
		public $disabled = false;
		public $postScripts;
		public $html;
		public $wraper;
		public $enableButtonOK = true;
		public $enableButtonCancel = true;
		public $buttonCancelCommand;
		public $fieldByRow;
		public $htmlFormAttrs;

		private $requireds;
		private $confirm_icon;
		private $confirm_msg;


		public ?AbstractBaseDAO $validationDAO = null;

		const FIELD_TYPE_TEXT = "text";
		const FIELD_TYPE_HIDDEN = "hidden";
		const FIELD_TYPE_LABEL = "label";
		const FIELD_TYPE_PASSWORD = "password";
		const FIELD_TYPE_TEXTAREA = "textarea";
		const FIELD_TYPE_RADIO = "radio";
		const FIELD_TYPE_CHECK = "check";
		const FIELD_TYPE_SELECT = "select";
		const FIELD_TYPE_SELECT_I18N = "select-i18n";
		const FIELD_TYPE_SELECT_ARRAY = "select-array";
		const FIELD_TYPE_DIV = "div";
		const FIELD_TYPE_SEARCH_SELECT = "search_select";
		const FIELD_TYPE_MULTIPLE_SELECT = "multiple_select";
		const FIELD_TYPE_DATE = "date";
		const FIELD_TYPE_DATETIME = "datetime";
		const FIELD_TYPE_EMAIL = "email";
		const FIELD_TYPE_FILE = "file";
        const FIELD_TYPE_TIME = "time";
		const FIELD_TYPE_CHECK_ARRAY = "check-array";
		const FIELD_TYPE_TEXT_SEARCH = "text-search";

		public $show_names = true;
        /**
         * @var string|null Ruta de la plantilla para los campos del formulario.
         */
        private mixed $field_squema;

        private static string $generalSchema = "";
        private static string $generalFieldSchema = "";
        private array $colAttrs = [];


        /**
         * Constructor de la clase FormMaker.
         *
         * Crea una nueva instancia de FormMaker. Si se proporciona una ruta de plantilla (`$schema`),
         * la utiliza como plantilla para el formulario. De lo contrario, busca la ruta en `self::$generalSchema`
         * o utiliza una ruta por defecto. Lo mismo ocurre con la plantilla de campos (`$field_squema`).
         * Además, establece por defecto el comando para el botón de cancelar como un historial de retroceso.
         *
         * @param string|null $schema Ruta de la plantilla para el formulario (opcional).
         * @param string|null $field_squema Ruta de la plantilla para los campos del formulario (opcional).
         */
        function __construct($schema = null, $field_squema=null) {
            if($schema){
                $this->schema = $schema;
            }else if(self::$generalSchema != ""){
                $this->schema = self::$generalSchema;
            }else{
                $this->usePrivatePathInView=false;
            	$this->schema = Environment::getPath() .  "/views/common/form.php";
            }

            if($field_squema){
                $this->field_squema = $field_squema;
            }else if(self::$generalFieldSchema != ""){
                $this->field_squema = self::$generalFieldSchema;
            }else{
                $this->usePrivatePathInView=false;
                $this->field_squema = Environment::getPath() .  "/views/common/form_field.php";
            }

			//establese back por defecto al precionar cancelar
			$this->buttonCancelCommand = $this->historyBack();
        }

        /**
         * Establece el esquema general para todos los bloques Formularios.
         *
         * Este método estático permite establecer un esquema general que se utilizará
         * en todos los bloques DashViewer que se creen posteriormente.
         *
         * @param string $generalSchema El esquema general a establecer.
         * @return void
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }

        /**
         * Establece el esquema general para todos los bloques campos del formulario.
         *
         * Este método estático permite establecer un esquema general que se utilizará
         * en todos los bloques DashViewer que se creen posteriormente.
         *
         * @param string $generalFieldSchema El esquema general a establecer.
         * @return void
         */
        public static function setGeneralFieldSchema(string $generalFieldSchema): void
        {
            self::$generalFieldSchema = $generalFieldSchema;
        }



        /**
         * Define un campo en la configuración del formulario.
         *
         * Este método permite definir un campo en la configuración del formulario mediante un arreglo de opciones.
         * Se pueden proporcionar diversas opciones para personalizar el comportamiento y la apariencia del campo,
         * como su etiqueta, tipo, fuente de datos, acciones de búsqueda, parámetros de búsqueda, acciones de
         * visualización, parámetros de visualización, contenido HTML personalizado, envoltura y requerimiento.
         *
         * @param array|FormMakerFieldConf $baseConf Configuración del campo. Puede ser un arreglo manualmente construido o generado utilizando el método build de la clase FormMakerFieldConf.
         * @return void
         */
		public function defineField(FormMakerFieldConf|array $baseConf): void
        {
            if($baseConf instanceof FormMakerFieldConf){
                $conf = $baseConf->build();
            }else{
                $conf = $baseConf;
            }

			//si es un arreglo
			if(is_array($conf)){

				if(isset($conf["campo"])){
					$campo = $conf["campo"];

					if(isset($conf["label"])){
						$this->legents[$campo] = $conf["label"];
					}

					if(isset($conf["tipo"])){
						if($conf["tipo"] == self::FIELD_TYPE_FILE){
							$this->encType=true;
						}

						$this->types[$campo] = $conf["tipo"];
					}else{
						$this->types[$campo] = self::FIELD_TYPE_TEXT;
					}

					if(isset($conf["source"])){
						$this->sources[$campo] = $conf["source"];
					}

					if(isset($conf["action"])){
						$this->searchAction[$campo] = $conf["action"];
					}

					if(isset($conf["params"])){

						$this->searchParams[$campo] = $conf["params"];
					}

					if(isset($conf["showAction"])){
						$this->showAction[$campo] = $conf["showAction"];
					}

					if(isset($conf["showParams"])){

						$this->showParams[$campo] = $conf["showParams"];
					}

					if(isset($conf["html"])){

						$this->html[$campo] = $conf["html"];
					}

					if(isset($conf["wraper"])){

						$this->wraper[$campo] = $conf["wraper"];
					}

					if(isset($conf["required"])){

						$this->requireds[$campo] = $conf["required"];
					}else{
						$this->requireds[$campo] = false;
					}

				}
			}
		}



		private function buildParams(){

			$this->params = $this->getAllVars();
		}

        /**
         * Agrega los scripts de JavaScript al final del formulario y, opcionalmente, los muestra en la salida.
         *
         * @param bool $autoshow Indica si se deben mostrar los scripts en la salida automáticamente.
         * @return string Cadena que contiene todos los scripts agregados.
         */
		private function putPostScripts($autoshow = true){
			$all = "";

			if($this->postScripts){
				if(is_array($this->postScripts)){
					foreach ($this->postScripts as $key => $script) {
						$all .= " <script>" . $script . "</script>";
					}
				}else{
					$all .=  " <script>" . $this->postScripts . "</script>";
				}
			}

			if($autoshow){
				echo $all;
			}
			return $all;
		}

        /**
         * Genera la acción para enviar el formulario y, opcionalmente, la muestra en la salida.
         *
         * @param bool $autoshow Indica si se debe mostrar automáticamente la acción en la salida.
         * @return string Cadena que contiene el script de la acción de envío del formulario.
         */
		function getSubmitAction($autoshow = true){
			$script = "$( '#".$this->name."' ).submit()";


			if($autoshow){
				echo $script;
			}

			return $script;
		}

        /**
         * Genera la apertura de un formulario
         * @return void
         */
		function openForm(){
			if($this->encType){
				$this->setVar("do", $this->actionDO);
			}

			if(!isset($this->resultID)){
				$this->resultID = Environment::$APP_HIDDEN_CONTENT;
			}

			$_enctype = "";
			if($this->encType){
				$_enctype = 'enctype="multipart/form-data"';
			}

			$params = "";
			if(isset($this->htmlFormAttrs)){
				$params = $this->genAttribs($this->htmlFormAttrs, false);
			}

			?>
			<form role="form" name="<?php echo $this->name; ?>" id="<?php echo $this->name; ?>" method='POST'
				action='<?php echo $this->action; ?>' <?php echo $_enctype; ?> <?php echo $params; ?>
		    <?php
		    if(!$this->encType){
		    ?>
    		onsubmit="send_form('<?php echo $this->name; ?>', '<?php echo $this->resultID;?>', '<?php echo $this->actionDO; ?>'); return false;"
		    <?php
		    }

			if($this->confirm_msg != ""){
				?>
				data-msg="<?php echo $this->confirm_msg; ?>" data-msgicon="<?php echo $this->confirm_icon; ?>"
				<?php
			}
		    ?>
			>
			<?php
		}

        /**
         * Genera el cierre de un formulario
         * @return void
         */
		function closeForm(){


			$this->buildParams();
			if(is_array($this->params)){
				foreach ($this->params as $paramName => $value) {
					if($value != ""){
						?>
						<input class="form-control"  type="hidden" name="<?php echo $paramName; ?>" value="<?php echo $value?>" />
						<?php
					}
				}
			}
			?>
			</form>
			<?php
			$this->putPostScripts();
		}

        /**
         * Genera la etiqueta HTML para un campo del formulario.
         *
         * @param string $campo Nombre del campo para el cual se generará la etiqueta.
         * @return string Etiqueta HTML generada para el campo.
         */
		function fieldMakeLabel($campo){
			$label = "";

			if (!isset($this->types[$campo]) ||
				($this->types[$campo] != self::FIELD_TYPE_DIV  &&
				$this->types[$campo] != self::FIELD_TYPE_HIDDEN &&
				$this->types[$campo] != self::FIELD_TYPE_TEXT_SEARCH)){


				if(isset($this->legents[$campo])){
					$label =  ucwords($this->legents[$campo]);
				}else{
					$label =  ucwords(showMessage($campo));
				}
				$label .= ":";
			}


			return "<label>" . $label . "</label>";
		}

        /**
         * Construye y muestra los campos del formulario siguiendo la configuración definida.
         */
		function buildAllFields(){
			$this->buildParams();
			$row_opened = false;

			$row = 0;
			$total_fields = count($this->prototype);
			$field_n = 0;
			$fields_in_row = 0;

			//si se definio algun campo
			if($this->prototype != null && count($this->prototype) > 0){
				foreach ($this->prototype as $campo => $value) {
					//muestra el nombre del campo
					$this->showName($this->name, $campo);

					//numero de campo actual
					$field_n ++;

					//Si hay row definido
					if(isset($this->fieldByRow) && isset($this->fieldByRow[$row])){
						//la cantidad de campos es la configurada
						$config_field_cant = $this->fieldByRow[$row];
						$cols = $config_field_cant ;
					}else{
						//cantidad de campos en la fila es el total de campos;
						$config_field_cant = $total_fields;
						$cols = 1;
					}

					//si no esta abierta la fila
					if(!$row_opened){

						//establece la cantidad actual de campos en la fila
						$fields_in_row = 0;

						//open row
						echo '<div class="row" >';

						//establece que esta abierta la fila
						$row_opened = true;


					}

					//verifica si es un hidden
					if(isset($this->types[$campo]) && $this->types[$campo] == self::FIELD_TYPE_HIDDEN){
						$class_col = "hide";
					}else{
						$class_col = null;
					}

                    if(isset($this->colAttrs[$campo]["class"])){
                        $class_col = $this->colAttrs[$campo]["class"];

                        unset($this->colAttrs[$campo]["class"]);
                    }

					$wrap_cols = $this->fieldColWraper($cols, $class_col);
					$wrap = $this->fieldWraper($campo);

					echo $wrap_cols[0];
						echo $wrap[0];
							echo $this->fieldMakeLabel($campo);
							$fields_in_row ++;
							echo $this->fieldMake($campo, $value);
						echo $wrap[1];
					echo $wrap_cols[1];


					//si esta abierta la tabla
					if($row_opened){
						//si la cantidad de campos en ella es igual al total de campos configurados por fila o el campo es el ultimo
						if($fields_in_row == $config_field_cant || $field_n == $total_fields){
							//close row
							echo '</div >';

							//establece que esta cerrada la fila
							$row_opened = false;

							//aumenta la cantidad de filas impresas
							$row++;
						}
					}
				}
			}
		}

        /**
         * Construye un envoltorio del campo
         */
		function fieldWraper($campo){
			//obtiene clase de requerido
			//$req_class = ($this->requireds[$campo])? "is-invalid" : "";

			$open_wrap = "<div class='form-group ' ";
			$close_wrap = "</div>";



			if(isset($this->wraper[$campo])){
				$open_wrap .= " name='" . $this->wraper[$campo] . "'";
				$open_wrap .= " id='" . $this->wraper[$campo] . "'";
			}
			$open_wrap .= ">";


			return array($open_wrap,$close_wrap);
		}

        /**
         * Reemplaza los marcadores en el texto (%23 y #) con los valores correspondientes de los datos proporcionados.
         * @example $text = '%23id%23'; $data = ['id'=> 1]; resultado = '1'
         * @example $text = '#id#'; $data = ['id'=> 1]; resultado = '1'
         * @param string $text Texto en el que se reemplazarán los marcadores.
         * @param array|null $data Datos que se utilizarán para reemplazar los marcadores (opcional). Si no se proporciona, se utilizarán los datos del formulario.
         * @return string Texto con los marcadores reemplazados por los valores correspondientes.
         */
		function incrustParams($text, $data = null){
			if(!$data){

				if($this->getAllVars() != null){
					$data = array_merge($this->prototype, $this->getAllVars());
				}else{
					$data = $this->prototype;
				}
			}

			foreach($data as $campo => $value){
				$text = str_replace("%23".$campo."%23", $value, $text);
				$text = str_replace("#".$campo."#", $value, $text);
			}

			return $text;

		}

        /**
         * Construye el campo basado en las configuraciones cargadas
         * @param $campo
         * @param $value
         * @return string
         */
		function fieldMake($campo, $value){
			if($this->sufix != ""){
				$nombreCampo = $this->prefix . $campo . "[]";
			}else{
				$nombreCampo = $this->prefix . $campo . $this->sufix;
			}

			$idCampo = $this->prefix . $campo . $this->sufix;

			//obtiene clase de requerido
			$req_class = ($this->requireds[$campo])? "is-invalid" : "";

			$attrs = (isset($this->html[$campo]))? $this->genAttribs($this->html[$campo], false) : null;
			$attrs = $this->incrustParams($attrs);

			$_disabled = "";
			if($this->disabled){
				$_disabled = " disabled ";
			}

			if(!isset($this->types[$campo])){
				$this->types[$campo] = self::FIELD_TYPE_TEXT;
			}


			$params = get_object_vars($this);
			$params["nombreCampo"] = $nombreCampo;
			$params["idCampo"] = $idCampo;
			$params["_disabled"] = $_disabled;
			$params["attrs"] = $attrs;
			$params["campo"] = $campo;
			$params["value"] = $value;
			$params["req_class"] = $req_class;

			return $this->display($this->field_squema, $params, false);
		}

        /**
         * Genera envoltorios de los campos
         * @param $cant
         * @param $class
         * @return string[]
         */
		function fieldColWraper($cant, $class=null){
			$open = "<div class='";
			$close = "</div>";

			if(!$class){
				switch ($cant) {
					case 1:
						$open .= "col-md-12";
					break;

					case 2:
						$open .= "col-md-6";
					break;

					case 3:
						$open .= "col-md-4";
					break;

					case 4:
						$open .= "col-md-3";
					break;

					default:
						$open .= "col-md-12";
					break;
				}
			}else{
				$open .= $class;
			}

			$open .= "'>";

			return array($open,$close);
		}

        /**
         * Genera y muestra los botones del formulario si no está deshabilitado.
         * Los botones generados pueden incluir el botón "ok" y el botón "cancelar" si están habilitados.
         */
		function formMakeButtons(): void
        {
			if(!$this->disabled)
			{

				//si esta habilitado mostrar el boton ok
				if($this->enableButtonOK){
					echo '<input class="btn btn-success "  type="submit" value="ok"  /> ';
				}

				//si esta habilitado mostrar el boton cancelar
				if($this->enableButtonCancel){

					echo '<input class="btn btn-default"  type="button" value="cancel" onclick="'. $this->buttonCancelCommand.'" /> ';

				}


			}
		}

        /**
         * Agrega una fila en la que se presentarán los siguientes campos como columnas.
         *
         * @param int $cant_fields Cantidad de campos que se presentarán como columnas en la fila.
         */
		function addRow(int $cant_fields): void
        {
			$this->fieldByRow[] = intval($cant_fields);
		}

        /**
         * Muestra el formulario generado según la configuración establecida.
         * Este método carga la configuración del formulario desde la base de datos si está habilitado, carga los campos requeridos desde la base de datos, y luego construye y muestra el formulario.
         */
		function show(){

			$this->loadFormConfigFromDB();
			$this->loadDBRequired();

			if($this->name != ""){

				$this->openForm();
			}

			$this->buildAllFields();


			if($this->name != ""){
				$this->formMakeButtons();
				$this->closeForm();
			}


		}

		/**
		 * Carga los campos que son requeridos por la base de datos
		 */
		private function loadDBRequired(): void
        {

			//para cada campo
			foreach($this->prototype as $field => $val){
				if(!isset($this->requireds[$field])){
					$this->requireds[$field] = false;
				}

				//si hay data de validación cargada
				if($this->validationDAO != null){
					if($this->validationDAO->checkFieldRequired($field, $this->prototype )){
						$this->requireds[$field] = true;
					}
				}
			}


		}

        /**
         * Carga la configuración del formulario desde la base de datos y la aplica a los campos correspondientes en la instancia actual.
         * Este método busca en la base de datos la configuración de los campos del formulario según el nombre del formulario, y luego aplica esa configuración a los campos correspondientes en la instancia del formulario.
         */
		public function loadFormConfigFromDB(): void
        {

			if($this->name && $this->name != ""){
				//busca en la configuración con el nombre del formulario
				$configDAO = new FormFieldConfigDAO();

				$configDAO->escaoeHTML_OFF();
				$configDAO->getByName($this->name);
				$all_config = $configDAO->fetchAll();
				$configDAO->escaoeHTML_ON();



                if($all_config) {
                    foreach ($all_config as $field_config) {
                        $field_name = $field_config["field_name"];

                        //si el camo esta en el prototipo
                        if (array_key_exists($field_name, $this->prototype)) {

                            //si el campo esta establecido como requerido
                            if ($field_config["required"] == SimpleDAO::REG_ACTIVO_Y) {

                                //carga el requerido
                                $this->requireds[$field_name] = true;
                            }

                            //carga el label
                            if ($field_config["label"] != "") {
                                $this->legents[$field_name] = $field_config["label"];
                            }

                            //carga el tipo
                            if ($field_config["type"] != "") {
                                $this->types[$field_name] = $field_config["type"];
                            }

                            //carga atributos html
                            if ($field_config["html_attrs"] != "") {

                                $attrs = $this->incrustParams($field_config["html_attrs"], array(
                                    "form_name" => $field_config["form_name"],
                                    "field_name" => $field_config["field_name"],
                                ));
                                $attrs = json_decode($attrs, true);

                                if ($attrs) {
                                    $this->html[$field_name] = $attrs;
                                }
                            }

                            //carga atributos html de la columna
                            if ($field_config["col_attributes"] != "") {

                                $attrs = $this->incrustParams($field_config["col_attributes"], array(
                                    "form_name" => $field_config["form_name"],
                                    "field_name" => $field_config["field_name"],
                                ));
                                $attrs = json_decode($attrs, true);

                                if ($attrs) {
                                    $this->colAttrs[$field_name] = $attrs;
                                }
                            }

                            //carga scripts
                            if ($field_config["post_script"] != "") {
                                $this->postScripts[] = $field_config["post_script"];
                            }


                            //si se configuro un source
                            if ($field_config["source_type"] != "") {

                                $source = null;

                                switch ($field_config["source_type"]) {

                                    case FormFieldConfigDAO::SOURCE_TYPE_ARRAY:
                                        $this->types[$field_name] = self::FIELD_TYPE_SELECT_ARRAY;

                                        if ($field_config["source_method"] != "") {
                                            //convierte a array
                                            $source = json_decode($field_config["source_method"], true);

                                        }
                                        break;

                                    case FormFieldConfigDAO::SOURCE_TYPE_DAO:

                                        ;
                                        $source = $this->getSourceDao($field_config);

                                        break;

                                    case FormFieldConfigDAO::SOURCE_TYPE_SQL:


                                        $source = $this->getSourceDaoFromSQL($field_config);

                                        break;


                                }

                                $this->sources[$field_name] = $source;
                            }
                        }
                    }
                }
			}
		}


        /**
         * Obtiene una instancia del objeto DAO especificado en la configuración y realiza una llamada al método de origen.
         *
         * @param array $field_config Configuración del campo obtenida de la base de datos.
         * @return AbstractBaseDAO|null Una instancia del objeto DAO configurado, o null si no se pudo crear la instancia o llamar al método.
         */
		private function getSourceDao(array $field_config): ?AbstractBaseDAO
        {
			/**
			 * @var $obj_dao AbstractBaseDAO
			 */
			$obj_dao = null;

			if($field_config["source_dao"] != ""){

				$className = $field_config["source_dao"];
				$method = $field_config["source_method"];
                $namespace = Environment::$NAMESPACE_MODELS;

				if(!class_exists($namespace  . $className)){
                    if(!class_exists($className)){
                        $namespace = "HandlerCore\\models\\dao\\";
                    }
				}




				if (class_exists($namespace  . $className)) {
                    $className = $namespace  . $className;
					$obj_dao = new $className();
					$obj_dao->selectID = $field_config["source_id"];
					$obj_dao->selectName = $field_config["source_name"];

					if(method_exists($obj_dao, $method)){
						$obj_dao->$method();
					}

				}
			}

			return $obj_dao;
		}

        /**
         * Obtiene una instancia del objeto DAO a partir de una consulta SQL y configura las propiedades selectID y selectName.
         *
         * @param array $field_config Configuración del campo obtenida de la base de datos.
         * @return AbstractBaseDAO|null Una instancia del objeto DAO configurado, o null si no se pudo crear la instancia o la consulta SQL está vacía.
         */
		private function getSourceDaoFromSQL(array $field_config): ?AbstractBaseDAO
        {

			$obj_dao = null;

			if($field_config["source_method"] != "" ){

				$sql = $field_config["source_method"];


				$sql = ReporterMaker::embedParams($sql, SimpleDAO::putQuoteAndNull($this->prototype));
				$sql = ReporterMaker::embedParams($sql, SimpleDAO::putQuoteAndNull($this->getAllVars()));

				$obj_dao = ReporterMaker::getDAOFromSQL($sql);
				$obj_dao->selectID = $field_config["source_id"];
				$obj_dao->selectName = $field_config["source_name"];


			}

			return $obj_dao;
		}

        /**
         * Establece el mensaje de confirmación que se mostrará antes de enviar el formulario.
         *
         * @param string $msg Mensaje de confirmación a mostrar.
         * @param string|null $icon Icono que se utilizará para el mensaje de confirmación (por defecto: "success").
         * @return void
         */
		function setConfirmMsg(string $msg, $icon=null): void
        {
			$this->confirm_msg =$msg;

			if(!$icon){
				$icon = "success";
			}

			$this->confirm_icon = $icon;
		}

        /**
         * Muestra el nombre del formulario y el campo en un llamativo recuadro si la opción de mostrar nombres está habilitada tanto en el formulario como en DynamicSecurityAccess.
         *
         * @param string $name Nombre del formulario.
         * @param string $field Nombre del campo.
         * @return void
         */
		private function showName($name, $field): void
        {



			if($this->show_names && DynamicSecurityAccess::$show_names){

				$link = Handler::make_link("<h5><i class='fas fa-cogs'></i> Form: $name</h5>",
					Handler::asyncLoad("FormFieldConfig",  Environment::$APP_CONTENT_BODY, array(
						"do"=>"form",
						"form_name"=>$name,
						"field_name"=>$field
					),true),
					false
				);
				echo "<div class='callout callout-danger'>
							$link
							Field: $field
						</div>";
			}

		}

        /**
         * Reenvía los parámetros de consulta recibidos por POST al formulario, excepto el parámetro "do".
         *
         * Este método recibe los parámetros de consulta enviados por POST y los establece como variables en el formulario, para mantener los valores ingresados por el usuario en caso de que el formulario necesite ser recargado.
         *
         * @return void
         */
		public function resendQueryParams(): void
        {
			$params = $_POST;
			$except = array("do");
			foreach ($params as $key => $value) {

				if(!in_array($key, $except)){
					$this->setVar($key, $value);
				}
			}

		}
    }


