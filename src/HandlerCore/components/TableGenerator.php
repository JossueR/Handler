<?php
namespace HandlerCore\components;
    use HandlerCore\Environment;
    use HandlerCore\models\dao\AbstractBaseDAO;
    use function HandlerCore\showMessage;

    /**
     *
     */
    class TableGenerator extends Handler{
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
		public $squema;

		/**
		 * se debe asociar a una funcio q tom un parametro.
		 * debe retornar un arreglo
		 * la funcion sera evaluda en cada fila
		 */
		public  $rowClausure = null;

		/**
		 * se debe asociar a una funcio q tom un parametro.
		 * debe retornar un arreglo
		 * la funcion sera evaluda en cada columna
		 * function($row, $field, $isTotal)
		 * return array("data"=>, "style"=>)
		 */
		public  $colClausure=null;
		public  $actionClausure=null;


		public  $totalsClausure=null;
		public  $totalVerticalClausure=null;
		public  $html = array();


		private $dbFields;
		private  $invoker;





        function __construct( AbstractBaseDAO $dao, $invoker=null) {
            $this->dao = $dao;
			$this->bookmark = new Bookmark($invoker);

			//si no se envio el invocador
			if(!$invoker || $invoker == ""){
				//desactiva los bookmarks
				$this->disableBookmark();
			}

			//muestra el sql si se habilita el modo depuración
			if($_SESSION['SQL_SHOW']){
				echo $invoker;
			}
			$this->show_labels = true;

			$this->squema = 'views/common/generalTable.php';
			$this->invoker = $invoker;
        }

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
				$this->display($this->squema, get_object_vars($this));

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

		//remueve los campos inesesarios
		private function clearfields(){

			//busca si se envio por post los campos a mostrar
			$fields_all = explode(",", $this->getRequestAttr("SHOW_FIELDS"));

			if(count($fields_all) > 1 ){
				foreach ($this->fields as $key => $value) {
					//si no esta el campo en la lista de campos a mostrar
					if(!in_array($value, $fields_all)){

						//quita los campos que no se quieren mostrar
						unset ($this->fields[$key]);
					}
				}
			}
		}

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

		static function removeOrder(){

			if(isset($_POST['FIELD'])){
				unset($_POST['FIELD']);
			}

			if(isset($_POST['ASC'])){
				unset($_POST['ASC']);
			}

		}

		public function enableBookmark(){
			$this->bookmarkEnabled = true;
		}

		public function disableBookmark(){
			$this->bookmarkEnabled = false;
		}

		public function getPage(){
			$page = 0;
			if(isset($_POST[Bookmark::$page])){
				$page = $_POST[Bookmark::$page];
			}
			return $page;
		}

		public function getRequestSearchFilter(){
			$search = "";
			if(isset($_POST[Bookmark::$search_filter])){
				$search = $_POST[Bookmark::$search_filter];
			}
			return $search;
		}

		public function getSearch(){
			$search = "";
			if(isset($_POST[Bookmark::$search_filter])){
				$search = $_POST[Bookmark::$search_filter];
			}
			return $search;
		}

		public function getOrderField(){
			$search = "";
			if(isset($_POST[Bookmark::$order_field])){
				$search = $_POST[Bookmark::$order_field];
			}
			return $search;
		}

		public function getOrderType(){
			$search = "";
			if(isset($_POST[Bookmark::$order_type])){
				$search = $_POST[Bookmark::$order_type];
			}
			return $search;
		}

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

		public function resendQueryParams(){
			$params = $_POST;
			$exept = array("do","ASC","FIELD","FILTER","FILTER_KEYS","PAGE");
			foreach ($params as $key => $value) {
				//si no es ninguno de los parametros exeptuados
				if(!in_array($key, $exept)){
					$this->setVar($key, $value);
				}
			}

		}

		public function showLabels($labels=true){
			$this->show_labels = $labels;
		}
    }
