<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
	 *
	 */
	class WrapperViewer extends Handler {
		private $squema;
		public $name;
        public  $class = "row";
		private $data;

		const TYPE_RAW = "RAW";
		const TYPE_OBJ = "OBJ";
		const TYPE_PATH = "PATH";



		function __construct($squema = null) {

            if($squema){
            	$this->squema = $squema;
            }else{
                $this->usePrivatePathInView=false;
            	$this->squema = Environment::getPath() .  "/views/common/wrapper.php";
            }

			$this->title=false;
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

			$this->display($this->squema, get_object_vars($this));
		}

	}
