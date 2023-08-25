<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
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
         * @param string $generalSchema
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }

		function addButton($key, $config){
			$this->buttons[$key] = $config;
		}

		function addManyButtons($config){
			$this->buttons =	array_merge($this->buttons, $config);
		}


		function show(){
			$this->display($this->schema, get_object_vars($this));
			$this->putPostScripts();
		}

		function showInGroup(){
			$this->in_group = true;
		}



		public function addPostScript($script, $have_script_tag=false){
			if(!$have_script_tag){
				$script = "<script>" . $script . "</script>";
			}

			$this->postSripts[] = $script;
		}

		public function putPostScripts(){
			if(isset($postSripts)){
				foreach ($postSripts as $script) {
					echo $script;
				}
			}
		}

		public function setParamsData($params)
		{
			$this->params_data = $params;
		}

		public function setName($name){
			$this->name = $name;
		}

		public function setShowLabel($show){
			$this->show_label = $show;
		}

	}
