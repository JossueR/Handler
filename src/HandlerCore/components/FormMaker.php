<?php
namespace HandlerCore\components;

    use HandlerCore\Environment;
    use HandlerCore\models\dao\AbstractBaseDAO;
    use HandlerCore\models\dao\FormFieldConfigDAO;
    use HandlerCore\models\SimpleDAO;
    use function HandlerCore\showMessage;

    /**
     *
     */
    class FormMaker extends Handler {

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
		private $squema;
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
         * @var mixed|string
         */
        private mixed $field_squema;


        function __construct($squema = null, $field_squema=null) {
            if($squema){
            	$this->squema = $squema;
            }else{
                $this->usePrivatePathInView=false;
            	$this->squema = Environment::getPath() .  "/views/common/form.php";
            }

            if($field_squema){
                $this->field_squema = $field_squema;
            }else{
                $this->usePrivatePathInView=false;
                $this->field_squema = Environment::getPath() .  "/views/common/form_field.php";
            }

			//establese back por defecto al precionar cancelar
			$this->buttonCancelCommand = $this->historyBack();
        }

		/**
		 * $conf es un arreglo que admite:
		 * campo
		 * label
		 * tipo
		 */
		public function defineField($conf = array()){

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

		public function _show(){

			if($this->encType){
				$this->setVar("do", $this->actionDO);
			}

			if(!isset($this->resultID)){
				$this->resultID = Environment::$APP_HIDDEN_CONTENT;
			}

			$this->buildParams();
			$this->display($this->squema, get_object_vars($this));
			$this->putPostScripts();
		}

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

		function getSubmitAction($autoshow = true){
			$script = "$( '#".$this->name."' ).submit()";


			if($autoshow){
				echo $script;
			}

			return $script;
		}

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

		function formMakeButtons(){
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

		function addRow($cant_fields){
			$this->fieldByRow[] = intval($cant_fields);
		}

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

		private function loadFormConfigFromDB(): void
        {

			if($this->name && $this->name != ""){
				//busca en la configuración con el nombre del formulario
				$configDAO = new FormFieldConfigDAO();

				$configDAO->escaoeHTML_OFF();
				$configDAO->getByName($this->name);
				$all_config = $configDAO->fetchAll();
				$configDAO->escaoeHTML_ON();




				foreach ($all_config as $field_config) {
					$field_name = $field_config["field_name"];

					//si el camo esta en el prototipo
					if(array_key_exists($field_name, $this->prototype)){

						//si el campo esta establecido como requerido
						if($field_config["required"] == SimpleDAO::REG_ACTIVO_Y){

							//carga el requerido
							$this->requireds[$field_name] = true;
						}

						//carga el label
						if($field_config["label"] != ""){
							$this->legents[$field_name] = $field_config["label"];
						}

						//carga el tipo
						if($field_config["type"] != ""){
							$this->types[$field_name] = $field_config["type"];
						}

						//carga atributos html
						if($field_config["html_attrs"] != ""){

							$attrs = $this->incrustParams($field_config["html_attrs"], array(
								"form_name"=>$field_config["form_name"],
								"field_name"=>$field_config["field_name"],
							));
							$attrs = json_decode($attrs,true);

							if($attrs){
								$this->html[$field_name] = $attrs;
							}
						}

						//carga scripts
						if($field_config["post_script"] != ""){
							$this->postScripts[] = 	$field_config["post_script"];
						}


						//si se configuro un source
						if($field_config["source_type"] != ""){

							$source = null;

							switch ($field_config["source_type"]) {

								case FormFieldConfigDAO::SOURCE_TYPE_ARRAY:
									$this->types[$field_name] = self::FIELD_TYPE_SELECT_ARRAY;

									if($field_config["source_method"] != ""){
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


		private function getSourceDao($field_config){
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

		private function getSourceDaoFromSQL($field_config): ?AbstractBaseDAO
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

		function setConfirmMsg($msg, $icon=null){
			$this->confirm_msg =$msg;

			if(!$icon){
				$icon = "success";
			}

			$this->confirm_icon = $icon;
		}

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


