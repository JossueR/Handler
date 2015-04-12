<?php
	loadClass(PATH_FRAMEWORK . "components/Handler.php");
	
	/**
	 * 
	 */
	class DataViewer extends Handler {
		private $squema;
		private $title;
		private $dao;
		private $name;
		private $field_arr;
		public  $html = array();
		//arreglo con los nombre que se mostraran
		public  $legent=array();
		
		public  $fields=null;
		
		
		function __construct(AbstractBaseDAO $dao, $squema = null) {
			 $this->dao = $dao;
			 
            if($squema){
            	$this->squema = $squema;
            }else{
            	$this->squema = PATH_FRAMEWORK . "views/common/viewer.php";
            }
            
			$this->title=false;
        }
		
		function setTitle($title){
			$this->title = $title;
		}
		
		
		function show(){
			//si no se definieron los datos a mostrar, entonces muestra todos
			if($this->fields){
				$this->field_arr = explode(",", $this->fields);
				if(count($this->field_arr) < 1){
					$this->field_arr = $this->dao->getFields();
				}
			}else{
				$this->field_arr = $this->dao->getFields();
			}
			
			//para cada dato a mostrar, obtiene el 
			
			
			$this->display($this->squema, get_object_vars($this));
		}
		
	}
	
?>