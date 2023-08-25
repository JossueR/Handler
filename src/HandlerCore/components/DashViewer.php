<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
	 *
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
         * @param string $generalSchema
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }



		function setTitle($title){
			$this->title = $title;
		}


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

		private function  loadName(){
			if(!$this->name){
				$this->name = $this->getVar("name");
			}
		}

		public function addPostScript($script, $have_script_tag=false){
			if(!$have_script_tag){
				$script = "<script>" . $script . "</script>";
			}

			$this->postSripts[] = $script;
		}

		public function OnlyShowContent(){
			$this->only_show_content = true;
		}

	}

