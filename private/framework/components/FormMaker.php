<?php
	loadClass(PATH_FRAMEWORK . "components/Handler.php");
    /**
     * 
     */
    class FormMaker extends Handler {
		
        public $name;
		public $action;
		public $actionDO;
		public $prototype;
		public $legents;
		public $sources;
		public $types;
		public $resultID;
		public $params;
		public $searchAction;
		public $searchParams;
		public $showAction;
		public $showParams;
		private $squema;
		public $prefix;
		public $sufix;
		
		const FIELD_TYPE_TEXT = "text"; 
		const FIELD_TYPE_HIDDEN = "hidden";
		const FIELD_TYPE_LABEL = "label"; 
		const FIELD_TYPE_PASSWORD = "password"; 
		const FIELD_TYPE_TEXTAREA = "textarea"; 
		const FIELD_TYPE_RADIO = "radio"; 
		const FIELD_TYPE_CHECK = "check";  
		const FIELD_TYPE_SELECT = "select"; 
		const FIELD_TYPE_SELECT_I18N = "select-i18n"; 
		const FIELD_TYPE_SELECT_ARRAY = "select-array"; 
		const FIELD_TYPE_DIV = "div"; 
		const FIELD_TYPE_SEARCH_SELECT = "search_select"; 
		const FIELD_TYPE_MULTIPLE_SELECT = "multiple_select"; 
		const FIELD_TYPE_DATE = "date"; 
		const FIELD_TYPE_DATETIME = "datetime"; 
		const FIELD_TYPE_EMAIL = "email"; 

		
        function __construct($squema = null) {
            if($squema){
            	$this->squema = $squema;
            }else{
            	$this->squema = PATH_FRAMEWORK . "views/common/form.php";
            }
            
        }
		
		/**
		 * $conf es un arreglo que admite:
		 * campo
		 * label
		 * tipo
		 */
		public function defineField($conf = array()){
			
			//si es un arreglo
			if(is_array($conf)){
				
				if(isset($conf["campo"])){
					$campo = $conf["campo"];
					
					if(isset($conf["label"])){
						$this->legents[$campo] = $conf["label"];
					}
					
					if(isset($conf["tipo"])){
						$this->types[$campo] = $conf["tipo"];
					}else{
						$this->types[$campo] = 'text';
					}
					
					if(isset($conf["source"])){
						$this->sources[$campo] = $conf["source"];
					}
					
					if(isset($conf["action"])){
						$this->searchAction[$campo] = $conf["action"];
					}
					
					if(isset($conf["params"])){
						
						$this->searchParams[$campo] = $conf["params"];
					}
					
					if(isset($conf["showAction"])){
						$this->showAction[$campo] = $conf["showAction"];
					}
					
					if(isset($conf["showParams"])){
						
						$this->showParams[$campo] = $conf["showParams"];
					}
					
					if(isset($conf["html"])){
						
						$this->html[$campo] = $conf["html"];
					}
					
				}
			}
		}
		
		private function buildParams(){
			
			$this->params = $this->getAllVars();
		}
		
		public function show(){
			if(!isset($this->resultID)){
				$this->resultID = APP_HIDEN_CONTENT;
			}
			
			$this->buildParams();
			$this->display($this->squema, get_object_vars($this));
		}
    }
    
?>