<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
	 *
	 */
	class ListGroupViewer extends Handler implements ShowableInterface{
		private $schema;
		private $title;
		public  $html;
		public $main_text;
        public $subText;
        private $dao;
        public $colClausure;
        public $link;


		public  $fields=null;

        private static string $generalSchema = "";


		function __construct($dao, $schema = null) {
            $this->dao = $dao;

            if($schema){
                $this->schema = $schema;
            }else if(self::$generalSchema != ""){
                $this->schema = self::$generalSchema;
            }else{
            	$this->schema =  Environment::getPath() .  "/views/common/list.php";
            }

			$this->title=false;
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

			$this->display($this->schema, get_object_vars($this));
		}

	}
