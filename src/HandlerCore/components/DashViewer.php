<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
     * La clase DashViewer extiende la clase Handler y cumple con la interfaz ShowableInterface.
     * Esta clase se utiliza para generar HTML formateado que muestra información en un bloque.
     */
	class DashViewer extends Handler implements ShowableInterface{
		const BTN_ICON = "icon";
		const BTN_LINK = "link";
		const BTN_TYPE = "type";

		private $schema;
		//referencia de donde fue invokado
		private $invoker;

		private $title;
		private $name;
        public  $icon_class;
		public  $html = array();
		//arreglo con los nombre que se mostraran
		public  $legent=array();

		public  $fields=null;
		private $only_show_content;

		protected $postSripts;

        private static string $generalSchema = "";



        /**
         * Constructor de la clase DashViewer.
         *
         * @param mixed|null $inkoker El invocador para el bloque DashViewer.
         * @param string|null $schema El esquema que se utilizará para el bloque DashViewer actual.
         * @return void
         */
		function __construct($inkoker = null, $schema = null) {
			$this->invoker = $inkoker;

            if($schema){
            	$this->schema = $schema;
            }else if(self::$generalSchema != ""){
                $this->schema = self::$generalSchema;
            }else{
            	$this->schema = Environment::getPath() .  "/views/common/asociarWorkspace.php";
                $this->usePrivatePathInView=false;
            }

			$this->title=false;
			$this->only_show_content = false;

			$this->setVar("body_class", "table-responsive");
        }

        /**
         * Establece el esquema general para todos los bloques DashViewer.
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
         * Establece el título del bloque DashViewer.
         *
         * @param string $title El título que se desea asignar al bloque.
         * @return void
         */
		function setTitle($title){
			$this->title = $title;
		}


        /**
         * Muestra el contenido del bloque DashViewer.
         *
         * Este método muestra el contenido del bloque DashViewer si se cumplen ciertas condiciones
         * de seguridad y configuración. Si la opción `only_show_content` está habilitada, se mostrará
         * el contenido específico, como un formulario, contenido HTML o un script. De lo contrario,
         * se mostrará el contenido del bloque según el esquema y las propiedades del objeto.
         *
         * @return void
         */
		function show(){
			//carga el nombre si no esta
			$this->loadName();


			$sec = new DynamicSecurityAccess();
			if($sec->checkDash($this->invoker, $this->name)){
				if($this->only_show_content){
					$f = $this->getVar("f");
					$content = $this->getVar("content");
					$script = $this->getVar("script");
					$script_params = $this->getVar("script_params");

					if($f){
						$f->show();
					}else if($content){
						echo $content;
					}else if($script){
						$this->display($script,$script_params);
					}

				}else{
					$this->display($this->schema, get_object_vars($this));
				}

			}
		}

        /**
         * Carga el nombre del bloque si aún no se ha establecido, de las variables de configuración.
         *
         * @return void
         */
		private function  loadName(){
			if(!$this->name){
				$this->name = $this->getVar("name");
			}
		}

        /**
         * Agrega un script js para ejecutar después del contenido principal del bloque.
         *
         * @param string $script El script que se agregará.
         * @param bool $have_script_tag Indica si el script ya incluye las etiquetas <script>.
         * @return void
         */
		public function addPostScript($script, $have_script_tag=false){
			if(!$have_script_tag){
				$script = "<script>" . $script . "</script>";
			}

			$this->postSripts[] = $script;
		}

        /**
         * Habilita la opción para mostrar únicamente el contenido específico del bloque.
         *
         * Cuando esta opción está habilitada, se mostrará el contenido específico (formulario, HTML o script)
         * en lugar del esquema completo del bloque.
         *
         * @return void
         */
		public function OnlyShowContent(){
			$this->only_show_content = true;
		}

	}

