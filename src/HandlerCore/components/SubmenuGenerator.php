<?php
	
	/**
	 * 
	 */
	class SubmenuGenerator extends Handler {
		private $items = array();
		private $squema;
		public $title;
		
		public $squema_assoc;
	
		function __construct(){
			$this->squema = PATH_FRAMEWORK . "views/common/submenu.php";
		}
		
		public function addItem($text, $acction){
			$this->items[] = array($text,$acction);
		}
		
		public function clean(){
			$this->items = array();
		}
		
		public function show(){
			$this->display($this->squema, get_object_vars($this));
		}
	}
	
?>