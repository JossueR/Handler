<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
     * Clase que genera un envoltorio visual para bloques que implementen la interfaz ShowableInterface o sean vistas.
     */
	class WrapperViewer extends Handler implements ShowableInterface{
        /**
         * @var string La ruta de la vista a utilizar como esquema del envoltorio.
         */
        private $schema;

        /**
         * @var string El nombre del envoltorio.
         */
        public $name;

        /**
         * @var string La clase CSS a aplicar al envoltorio.
         */
        public $class = "row";

        /**
         * @var array|null Arreglo de datos a envolver.
         */
        private $data;

        /**
         * Constantes para definir los tipos de contenido.
         */
        const TYPE_RAW = "RAW";
        const TYPE_OBJ = "OBJ";
        const TYPE_PATH = "PATH";

        /**
         * @var string Ruta del esquema de envoltorio general.
         */
        private static string $generalSchema = "";
        /**
         * @var false
         */
        private bool $title;


        /**
         * Constructor de la clase.
         *
         * @param string|null $schema La ruta de la vista a utilizar como esquema del envoltorio.
         */
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
         * Establece la ruta del esquema de envoltorio general.
         *
         * @param string $generalSchema La ruta del esquema de envoltorio general.
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }


        /**
         * Agrega contenido al envoltorio.
         *
         * @param string|ShowableInterface $action El contenido a agregar.
         * @param string|null $type El tipo de contenido.
         *                         Puede ser uno de los valores: TYPE_RAW, TYPE_OBJ, TYPE_PATH.
         *                         Por defecto, si el contenido es un ShowableInterface, se establece TYPE_OBJ.
         */
		function add(string|ShowableInterface $action, $type=null){
			if(!$type || $action instanceof ShowableInterface){
				$type = self::TYPE_OBJ;
			}

			$this->data[] = array(
				"type"=>$type,
				"action"=>$action,
			);
		}


        /**
         * Muestra el envoltorio con su contenido.
         *
         * @return void
         */
		function show(){

			$this->display($this->schema, get_object_vars($this));
		}

	}
