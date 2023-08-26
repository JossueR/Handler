<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
     * Clase para generar una lista agrupada en forma de bloque HTML.
     */
	class ListGroupViewer extends Handler implements ShowableInterface{
        /**
         * @var string|null Ruta del archivo de esquema para generar el bloque HTML de la lista.
         */
        private $schema;

        /**
         * @var string|null Título que se mostrará en la lista agrupada.
         */
        private $title;

        /**
         * @var string|null Contenido HTML personalizado para incrustar en la lista.
         */
        public $html;

        /**
         * @var string|null Texto principal que se mostrará en la lista.
         */
        public $main_text;

        /**
         * @var string|null Texto secundario que se mostrará en la lista.
         */
        public $subText;

        /**
         * @var mixed El DAO utilizado para acceder a la base de datos y obtener los registros.
         */
        private $dao;

        /**
         * @var string|null Clausura HTML personalizada para cada columna en la lista.
         */
        public $colClausure;

        /**
         * @var string|null Enlace que se mostrará en la lista.
         */
        public $link;

        /**
         * @var array|null Campos de la lista que se mostrarán.
         */
        public $fields = null;

        /**
         * @var string Ruta general del archivo de esquema para generar el bloque HTML de la lista.
         */
        private static string $generalSchema = "";


        /**
         * Constructor de la clase ListGroupViewer.
         *
         * @param mixed $dao El DAO utilizado para acceder a la base de datos y obtener los registros.
         * @param string|null $schema Ruta del archivo de esquema para generar el bloque HTML de la lista.
         */
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
         * Establece la ruta general del archivo de esquema para generar el bloque HTML de la lista.
         *
         * @param string $generalSchema La ruta general del archivo de esquema.
         * @return void
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }

        /**
         * Establece el título que se mostrará en la lista agrupada.
         *
         * @param string $title El título a establecer.
         * @return void
         */
		function setTitle(string $title): void
        {
			$this->title = $title;
		}


        /**
         * Muestra el bloque HTML de la lista agrupada generada.
         *
         * @return void
         */
		function show(){

			$this->display($this->schema, get_object_vars($this));
		}

	}
