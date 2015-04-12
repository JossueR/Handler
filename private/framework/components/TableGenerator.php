<?php
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
		 */
		public  $colClausure=null;
		public  $actionClausure=null;
		
		
		public  $totalsClausure=null;
		public  $totalVerticalClausure=null;
		public  $html = array();
		
		
		private $dbFields;

		
		
		public function getUnicName(){
			
			 do{
					$sid = microtime(true);
					$sid = str_replace(".", "", $sid);
			}while ($sid == self::$LAST_UNIC);
			
			
			
			
			self::$LAST_UNIC = $sid;

			return $sid;
		}
        
        function __construct( AbstractBaseDAO $dao) {
            $this->dao = $dao;
			
			$this->squema = PATH_FRAMEWORK . 'views/common/generalTable.php';
        }
		
		public function show(){
			if($this->getRequestAttr(self::OUTPUT_FORMAT) == self::FORMAT_EXCEL){
				$this->outputExcel();
			}
			
			//genera un nombre unico, si no se envio alguno
			if(!$this->name){
				$this->name = $this->getUnicName();
			}
			
			
			$sumary = $this->dao->getSumary();
			if($sumary->allRows >= 0){
				$this->dbFields = $this->dao->getFields();
				
				//si no se especificaron los controles oara mostrar
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
				
				//si no se envia arreglo de etiquetas, usara el nombre de los campos que vienen de la base de datos
				$this->legent =  (is_null($this->legent))? $this->defaultFields() : $this->legent;
				
				//si no se envia orden por defecto, tomara el orden y campos q se envian de el query
				$this->fields = (empty($this->fields))?  array_keys($this->legent) : explode(",",$this->fields);
				

				$this->clearfields();

				$this->buildParams();
				$this->display($this->squema, get_object_vars($this));
			}
		}

		private function defaultFields(){
			$rel = array();
			foreach ($this->dbFields as $index => $key) {
				$rel[$key] = $this->dbFields[$index];
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
			
		}
		
		static function removeOrder(){
			
			if(isset($_POST['FIELD'])){
				unset($_POST['FIELD']);
			}
			
			if(isset($_POST['ASC'])){
				unset($_POST['ASC']);
			}
			
		}
    }
    
?>