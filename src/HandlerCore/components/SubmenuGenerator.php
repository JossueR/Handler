<?php
namespace HandlerCore\components;
	use HandlerCore\Environment;

    /**
	 *
	 */
	class SubmenuGenerator extends Handler implements ShowableInterface{
		private $items = array();
		private $schema;
		public $title;

        private static string $generalSchema = "";

		function __construct(){
            $this->usePrivatePathInView=false;
			$this->schema = Environment::getPath() .  "/views/common/submenu.php";
		}

		public function addItem($text, $acction){
			$this->items[] = array($text,$acction);
		}

		public function clean(){
			$this->items = array();
		}

		public function show(){
			$this->display($this->schema, get_object_vars($this));
		}

        /**
         * @param string $generalSchema
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }
	}
