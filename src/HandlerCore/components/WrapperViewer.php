<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
	 *
	 */
	class WrapperViewer extends Handler implements ShowableInterface{
		private $schema;
		public $name;
        public  $class = "row";
		private $data;

		const TYPE_RAW = "RAW";
		const TYPE_OBJ = "OBJ";
		const TYPE_PATH = "PATH";

        private static string $generalSchema = "";



		function __construct($schema = null) {

            if($schema){
                $this->schema = $schema;
            }else if(self::$generalSchema != ""){
                $this->schema = self::$generalSchema;
            }else{
                $this->usePrivatePathInView=false;
            	$this->schema = Environment::getPath() .  "/views/common/wrapper.php";
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

		function add($action, $type=null){
			if(!$type){
				$type = self::TYPE_OBJ;
			}

			$this->data[] = array(
				"type"=>$type,
				"action"=>$action,
			);
		}


		function show(){

			$this->display($this->schema, get_object_vars($this));
		}

	}
