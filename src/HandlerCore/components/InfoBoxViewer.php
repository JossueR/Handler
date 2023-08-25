<?php
namespace HandlerCore\components;


	use HandlerCore\Environment;

    class InfoBoxViewer extends Handler implements ShowableInterface{
		private $schema;
		public  $title;
		public  $html;
		public $icon;
        public $cant;
        public $subtitle;
        public $link;
        private $type;
        private  $name;
		public $class;
		private $counter;
		private $scripts;
		public $bar_percent;


        const CLASSTYPE_INFO = "card-info";
        const CLASSTYPE_WARNING = "card-warning";
        const CLASSTYPE_DANGER = "card-danger";
        const CLASSTYPE_SUCCESS = "card-success";

		const TYPE_DEFAULT = "infoBox.php";
		const TYPE_WITH_BACKGROUND = "infoBox_with_bg.php";
		const TYPE_WITH_BAR = "infoBox_with_bar.php";

        private static string $generalSchema = "";


		public  $fields=null;


		function __construct($name, $type=null, $schema=null) {
			switch ($type) {
				case self::TYPE_WITH_BACKGROUND :
				case self::TYPE_WITH_BAR :
					$this->type = $type;
				break;

				default:
					$this->type = self::TYPE_DEFAULT;
				break;
			}

            $this->name = $name;
            $this->usePrivatePathInView=false;

            if($schema){
                $this->schema = $schema;
            }else if(self::$generalSchema != ""){
                $this->schema = self::$generalSchema;
            }else{
                $this->schema = Environment::getPath() .  "/views/common/" . $this->type;
                $this->usePrivatePathInView=false;
            }
            $this->scripts = array();

			$this->title=false;
        }

        /**
         * @param string $generalSchema
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }




		function show(){

			$this->display($this->schema, get_object_vars($this));

			$this->finalScripts();
		}

		function setCounter($status){
			$this->counter = ($status == true);
		}

		function addScript($script){
			$this->scripts[] = $script;
		}


		private function finalScripts(){
			foreach ($this->scripts as $script) {
                echo "<script>$script</script>";

			}
		}


	}
