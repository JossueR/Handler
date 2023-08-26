<?php
namespace HandlerCore\components;
    use HandlerCore\Environment;
    use HandlerCore\models\dao\AbstractBaseDAO;
    use function HandlerCore\showMessage;

    /**
     * Clase que genera tablas HTML filtrables, ordenables y paginables basadas en el último query ejecutado en un objeto AbstractDAO.
     */
    class TableGenerator extends Handler implements ShowableInterface{
    	const CONTROL_ORDER = "ORDER";
		const CONTROL_FILTER = "FILTER";
		const CONTROL_PAGING = "PAGING";
		const CONTROL_GROUP = "GROUP";
		const CONTROL_SORT_FIELD= "SORT_FIELD";
		const CONTROL_FILTER_ADV= "FILTER_ADV";

		private static $LAST_UNIC;
		private static $LAST_COUNT;
		private $dao;
		private $bookmark;
		private $bookmarkEnabled = true;
		private $show_labels;

		public  $name=null;
    	public  $reloadScript=null;
		public  $reloadDo=null;

		//arreglo de acciones con las cuales se generaran los botones de accion
		public $actions=false;

		//arreglo con los nombre que se mostraran
		public  $legent=null;

		public  $fields=null;
		public  $controls=null;
		public $params = array();

		public $pagin = true;
		public $schema;

        /**
         * @var callable|null Una función que permite configurar los atributos HTML para cada fila generada en la tabla.
         * La función recibe un arreglo con los datos de la fila a construir y debe retornar un arreglo con los atributos HTML
         * que se generarán para la fila.
         *
         * @example Ejemplo:
         * function($row){
         *     $result["style"] = "background: #efe970";
         *     return $result;
         * };
         */
		public  $rowClausure = null;

        /**
         * @var callable|null Una función que permite configurar la apariencia y el contenido de una columna en la tabla.
         * La función recibe un arreglo con los datos de la fila, el nombre del campo de la columna y un indicador de si es el campo
         * de los totales finales de la tabla. Debe retornar un arreglo con las propiedades que se utilizarán para la columna.
         *
         * Ejemplo:
         * function($row, $field, $isTotal) {
         *     $data = $row[$field];
         *     return array("data" => $data, "style" => "border: 1px", "class" => "text-primary");
         * };
         */
		public  $colClausure=null;

        /**
         * @var callable|null Una función que permite configurar si se deben o no generar las acciones para la fila generada.
         * La función recibe un arreglo con los datos de la fila a construir y
         * debe retornar true o false para indicar si debe o no generar las acciones en esa fila.
         *
         * @example Ejemplo:
         * function($row){
         *
         *     return true;
         * };
         */
		public  $actionClausure=null;


        /**
         * @var callable|null Una función que permite acumular los totales de las columnas para generar una fila de totales al final de la tabla.
         * La función recibe un arreglo acumulador de totales y los datos de la fila actual. Debe devolver el arreglo de totales actualizado.
         *
         * @example Ejemplo:
         * function($totals, $row) {
         *     if (!isset($totals["amount"])) {
         *         $totals["amount"] = 0;
         *     }
         *     $totals["amount"] += $row["amount"];
         *     return $totals;
         * };
         */
		public  $totalsClausure=null;
		public  $totalVerticalClausure=null;
		public  $html = array();


		private $dbFields;
		private  $invoker;

        private static string $generalSchema = "";





        /**
         * Constructor de la clase.
         *
         * @param AbstractBaseDAO $dao El objeto DAO que proporciona los datos para la tabla.
         * @param string|null $invoker El invocador que originó la tabla.
         */
        function __construct( AbstractBaseDAO $dao, $invoker=null) {
            $this->dao = $dao;
			$this->bookmark = new Bookmark($invoker);

            // Si no se envió el invocador, desactiva los bookmarks
			if(!$invoker || $invoker == ""){
				$this->disableBookmark();
			}

            // Muestra el SQL si se habilita el modo depuración
			if($_SESSION['SQL_SHOW']){
				echo $invoker;
			}
			$this->show_labels = true;

            $this->usePrivatePathInView=false;
            if(self::$generalSchema != ""){
                $this->schema = self::$generalSchema;
            }else{
                $this->schema = Environment::getPath() .  "/views/common/generalTable.php";
                $this->usePrivatePathInView=false;
            }

			$this->invoker = $invoker;
        }

        /**
         * @param string $generalSchema
         */
        public static function setGeneralSchema(string $generalSchema): void
        {
            self::$generalSchema = $generalSchema;
        }

        /**
         * Genera la tabla
         * @return void
         */
		public function show(){
			//si están habilitados los bookmarks
			if($this->bookmarkEnabled){
				$f = (is_array($this->fields))? implode(",", $this->fields) : $this->fields;
				$this->bookmark->loadBookmark($f);
				$this->dao->findLast();
			}

			if($this->getRequestAttr(self::OUTPUT_FORMAT) == self::FORMAT_EXCEL){
				$this->outputExcel();
			}

			//genera un nombre único, si no se envió alguno
			if(!$this->name){
				$this->name = $this->getUnicName();
			}


			$sumary = $this->dao->getSumary();
			if($sumary->allRows >= 0){
				$this->dbFields = $this->dao->getFields();

				//si no se especificaron los controles ara mostrar
				if(is_null($this->controls)){
					$this->controls[] = self::CONTROL_ORDER;
					$this->controls[] = self::CONTROL_FILTER;
					$this->controls[] = self::CONTROL_PAGING;


					if($_SESSION["fullcontrols"])
					{
						//$this->controls[] = self::CONTROL_SORT_FIELD;
                        $this->controls[] = self::CONTROL_FILTER_ADV;
						$this->controls[] = self::CONTROL_GROUP;
					}
				}

				//si no se envía arreglo de etiquetas, usará el nombre de los campos que vienen de la base de datos
				$this->legent =  (is_null($this->legent))? $this->defaultFields() : $this->legent;

				//si no se envía orden por defecto, tomará el orden y campos que se envían del query
				if(empty($this->fields)){
					$this->fields = array_keys($this->legent);
				}else{
					if(!is_Array($this->fields)){
						$this->fields = explode(",",$this->fields);
					}

				}

				$this->clearfields();



				$this->buildParams();
				$this->display($this->schema, get_object_vars($this));

				$this->showTableControls($this->dao->autoconfigurable);
				Bookmark::unloadBookmarks();
			}
		}

		private function defaultFields(){
			$rel = array();
			foreach ($this->dbFields as $index => $key) {
				$rel[$key] = showMessage($this->dbFields[$index]);
			}

			return $rel;
		}

		//remueve los campos innecesarios
		private function clearfields(){

			//busca si se envío por post los campos a mostrar
			$fields_all = explode(",", $this->getRequestAttr("SHOW_FIELDS"));

			if(count($fields_all) > 1 ){
				foreach ($this->fields as $key => $value) {
					//si no está el campo en la lista de campos a mostrar
					if(!in_array($value, $fields_all)){

						//quita los campos que no se quieren mostrar
						unset ($this->fields[$key]);
					}
				}
			}
		}


        /**
         * Establece el nombre del generador de tabla. Si se proporciona un nombre, se utiliza ese nombre; de lo contrario, se genera un nombre único.
         *
         * @param string|null $name El nombre que se asignará al generador de tabla. Si se proporciona null o una cadena vacía, se generará un nombre único.
         * @return void
         */
		function setName($name){
			$this->name = ($name)? $name : $this->getUnicName();
		}

		private function buildParams(){

			$this->params = $this->getAllVars();
		}

		static function defaultOrder($field, $asc = true){
			/*
			if(!isset($_POST['FIELD'])){
				$_POST['FIELD'] = $field;
			}

			if(!isset($_POST['ASC'])){
				if($asc){
					$asc = 'A';
				}else{
					$asc = 'D';
				}

				$_POST['ASC'] = $asc;
			}
			*/
		}

        /**
         * Remueve las claves 'FIELD' y 'ASC' del arreglo POST, utilizadas para el ordenamiento de la tabla.
         *
         * @return void
         */
		static function removeOrder(){

			if(isset($_POST['FIELD'])){
				unset($_POST['FIELD']);
			}

			if(isset($_POST['ASC'])){
				unset($_POST['ASC']);
			}

		}

        /**
         * Habilita el uso de bookmarks (marcadores) en la tabla generada.
         *
         * @return void
         */
		public function enableBookmark(){
			$this->bookmarkEnabled = true;
		}

        /**
         * Deshabilita el uso de bookmarks (marcadores) en la tabla generada.
         *
         * @return void
         */
		public function disableBookmark(){
			$this->bookmarkEnabled = false;
		}

        /**
         * Obtiene el número de página actual de la tabla desde el arreglo POST.
         *
         * @return int El número de página actual de la tabla.
         */
		public function getPage(){
			$page = 0;
			if(isset($_POST[Bookmark::$page])){
				$page = $_POST[Bookmark::$page];
			}
			return $page;
		}

        /**
         * Obtiene el filtro de búsqueda ingresado por el usuario desde el arreglo POST.
         *
         * @return string El filtro de búsqueda ingresado por el usuario.
         */
		public function getRequestSearchFilter(){
			$search = "";
			if(isset($_POST[Bookmark::$search_filter])){
				$search = $_POST[Bookmark::$search_filter];
			}
			return $search;
		}

        /**
         * Obtiene el filtro de búsqueda ingresado por el usuario desde el arreglo POST.
         *
         * @return string El filtro de búsqueda ingresado por el usuario.
         */
		public function getSearch(){
			$search = "";
			if(isset($_POST[Bookmark::$search_filter])){
				$search = $_POST[Bookmark::$search_filter];
			}
			return $search;
		}

        /**
         * Obtiene el campo por el cual se debe ordenar la tabla desde el arreglo POST.
         *
         * @return string El nombre del campo por el cual se debe ordenar la tabla.
         */
		public function getOrderField(){
			$search = "";
			if(isset($_POST[Bookmark::$order_field])){
				$search = $_POST[Bookmark::$order_field];
			}
			return $search;
		}

        /**
         * Obtiene el tipo de ordenamiento (ascendente o descendente) desde el arreglo POST.
         *
         * @return string El tipo de ordenamiento ("ASC" para ascendente, "DESC" para descendente).
         */
		public function getOrderType(){
			$search = "";
			if(isset($_POST[Bookmark::$order_type])){
				$search = $_POST[Bookmark::$order_type];
			}
			return $search;
		}

        /**
         * Muestra los controles de la tabla, como paginación, ordenamiento y filtros.
         *
         * @param bool $autoShow Si se debe mostrar automáticamente (por defecto: true).
         * @return string|null El comando JavaScript para mostrar los controles.
         */
		private function showTableControls($autoShow = true){
			$page = 0;
			$order_field = "";
			$order_type = "";
			$search = "";

			//si estan habilitados los bookmarks
			if($this->bookmarkEnabled){
				$page = $this->bookmark->getPage();
				$order_field = $this->bookmark->getOrderField();
				$order_type = $this->bookmark->getOrderType();
				$search = $this->bookmark->getSearch();
			}else{
				//si no esta habilidado los bookmarks, busca los parametros en el post
				$page = $this->getPage();
				$order_field = $this->getOrderField();
				$order_type = $this->getOrderType();
				$search = $this->getSearch();

			}

			if($this->pagin){

				$this->params["do"] = $this->reloadDo;
				$this->params["objName"] = $this->name;

				$params = http_build_query($this->params, '', '&');

				$opts = array(
					"dest" => $this->name,
					"action" => $this->reloadScript,
					"params" => $params,
					"Pagination" => array(
						"show" => in_array(self::CONTROL_PAGING, $this->controls),
						"totalRows" => $this->dao->getNumAllRows(),
						"pageActual" => $page,
						"maxPerPage" => Environment::$APP_DEFAULT_LIMIT_PER_PAGE
					),
					"Sort" => array(
						"show" => in_array(self::CONTROL_ORDER, $this->controls),
						"orderField" => $order_field,
						"asc" => $order_type
					),
					"Filter" => array(
						"show" => in_array(self::CONTROL_FILTER, $this->controls),
						"adv" => in_array(self::CONTROL_FILTER_ADV, $this->controls),
						"filterKeys" => implode(",", $this->fields),
						"_filterText" => $search
					)
				);

				$json_opts = json_encode($opts);


				//$this->showPagination($this->name, $this->dao->getNumAllRows(), $this->reloadScript, $params, $this->controls);
				$command = "showTableControls($json_opts)";

				if($autoShow){
					echo "<script>$command</script>";
				}

				return $command;
			}


		}

        /**
         * Reenvía los parámetros de la consulta recibidos a las variables del objeto.
         */
		public function resendQueryParams(): void
        {
			$params = $_POST;
			$exept = array("do","ASC","FIELD","FILTER","FILTER_KEYS","PAGE");
			foreach ($params as $key => $value) {
				//si no es ninguno de los parametros exeptuados
				if(!in_array($key, $exept)){
					$this->setVar($key, $value);
				}
			}

		}

        /**
         * Habilita o deshabilita la visualización de etiquetas en la tabla.
         *
         * @param bool $labels True para mostrar las etiquetas, False para ocultarlas.
         */
		public function showLabels($labels=true): void
        {
			$this->show_labels = $labels;
		}
    }
