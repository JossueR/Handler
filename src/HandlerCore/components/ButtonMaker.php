<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
     * Clase para generar grupos de botones.
     *
     * La clase ButtonMaker se utiliza para generar grupos de botones de acuerdo a un esquema específico.
     * Puede configurarse con botones individuales o múltiples botones.
     *
     */
	class ButtonMaker extends Handler implements ShowableInterface {
		const BTN_ICON = "icon";
		const BTN_LINK = "link";
		const BTN_TYPE = "type";

		private $schema;
		//referencia de donde fue invokado
		private $invoker;
		private $buttons;
		private $name;
		private $in_group;
		private $params_data;
		private $show_label;

		protected $postSripts;

        private static string $generalSchema = "";


        /**
         * Constructor de la clase ButtonMaker.
         *
         * @param string $name El nombre del grupo de botones.
         * @param mixed $inkoker (Opcional) El invocador del grupo de botones.
         * @param mixed $schema (Opcional) El esquema a utilizar para mostrar los botones.
         */
		function __construct($name, $inkoker = null, $schema = null) {
			$this->name = $name;
			$this->invoker = $inkoker;

            if($schema){
                $this->schema = $schema;
            }else if(self::$generalSchema != ""){
                $this->schema = self::$generalSchema;
            }else{
                $this->usePrivatePathInView=false;
            	$this->schema = Environment::getPath() .  "/views/common/button.php";
            }

			$this->buttons = array();
			$this->in_group = false;
			$this->params_data = array();
			$this->show_label = true;
        }

        /**
         * Establece el esquema general para todos los grupos de botones.
         *
         * @param string $generalSchema El esquema general para mostrar los botones.
         * @return void
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }

        /**
         * Agrega un botón al grupo de botones con la configuración proporcionada.
         *
         * Este método permite agregar un botón al grupo de botones con la configuración especificada.
         * La configuración debe proporcionarse en forma de un arreglo asociativo con claves que indican las propiedades del botón.
         *
         * @param string $key La clave para identificar el botón en el grupo.
         * @param array $config Un arreglo asociativo que contiene la configuración del botón.
         *                     Las claves posibles son:
         *                       - "icon": El icono del botón en formato de clase CSS.
         *                       - "link": El enlace o acción que se ejecutará cuando se haga clic en el botón.
         *                       - "type": Clases CSS adicionales para personalizar el estilo del botón. Opcional.
         * @return void
         */
		function addButton($key, $config): void
        {
			$this->buttons[$key] = $config;
		}

        /**
         * Agrega múltiples botones al grupo de botones con la configuración proporcionada.
         *
         * Este método permite agregar varios botones al grupo de botones con la configuración especificada.
         * La configuración se proporciona en forma de un arreglo asociativo, donde cada clave representa una clave para
         * identificar el botón en el grupo y el valor asociado es un arreglo con la configuración del botón.
         *
         * @param array $config Un arreglo asociativo donde las claves son las claves de identificación de botón y los valores son
         *                      arreglos asociativos con la configuración del botón.
         *                      Cada arreglo de configuración debe contener las siguientes claves:
         *                       - "icon": El icono del botón en formato de clase CSS.
         *                       - "link": El enlace o acción que se ejecutará cuando se haga clic en el botón.
         *                       - "type": Clases CSS adicionales para personalizar el estilo del botón. Opcional.
         * @return void
         */
		function addManyButtons($config): void
        {
			$this->buttons =	array_merge($this->buttons, $config);
		}


        /**
         * Muestra el grupo de botones.
         *
         * Este método muestra el grupo de botones utilizando el esquema configurado.
         *
         * @return void
         */
		function show(): void
        {
			$this->display($this->schema, get_object_vars($this));
			$this->putPostScripts();
		}

        /**
         * Muestra el grupo de botones dentro de un grupo.
         *
         * Este método indica que el grupo de botones se mostrará dentro de un grupo mayor.
         *
         * @return void
         */
		function showInGroup(): void
        {
			$this->in_group = true;
		}



        /**
         * Agrega un script de JavaScript para ejecutarse después de mostrar los botones.
         *
         * Este método permite agregar un script de JavaScript que se ejecutará después de que se muestren los botones.
         * El script puede proporcionarse directamente o con etiquetas de script, según la bandera opcional.
         *
         * @param string $script El script de JavaScript que se agregará.
         * @param bool $have_script_tag Indica si el script ya incluye etiquetas de script. Valor predeterminado: false.
         * @return void
         */
		public function addPostScript($script, $have_script_tag=false): void
        {
			if(!$have_script_tag){
				$script = "<script>" . $script . "</script>";
			}

			$this->postSripts[] = $script;
		}

        /**
         * Imprime los scripts de JavaScript agregados posteriormente.
         *
         * Este método imprimirá los scripts de JavaScript agregados mediante el método addPostScript().
         * Si no se han agregado scripts, no se realizará ninguna acción.
         *
         * @return void
         */
		public function putPostScripts(): void
        {
			if(isset($postSripts)){
				foreach ($postSripts as $script) {
					echo $script;
				}
			}
		}

        /**
         * Establece los datos de parámetros para el grupo de botones.
         *
         * Este método permite establecer los datos de parámetros que se utilizarán en la generación de los botones.
         *
         * @param array $params Un arreglo de datos de parámetros para los botones.
         * @return void
         */
		public function setParamsData($params): void
        {
			$this->params_data = $params;
		}

        /**
         * Establece el nombre del grupo de botones.
         *
         * Este método permite establecer el nombre del grupo de botones, que se utilizará para identificarlo.
         *
         * @param string $name El nombre del grupo de botones.
         * @return void
         */
		public function setName($name): void
        {
			$this->name = $name;
		}

        /**
         * Establece si se mostrarán las etiquetas en los botones.
         *
         * Este método permite controlar si se mostrarán las etiquetas en los botones del grupo.
         *
         * @param bool $show Indica si se mostrarán las etiquetas en los botones.
         * @return void
         */
		public function setShowLabel($show): void
        {
			$this->show_label = $show;
		}

	}
