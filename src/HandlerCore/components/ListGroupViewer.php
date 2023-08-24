<?php
namespace HandlerCore\components;

	use HandlerCore\Environment;

    /**
	 *
	 */
	class ListGroupViewer extends Handler {
		private $squema;
		private $title;
		public  $html;
		public $main_text;
        public $subText;
        private $dao;
        public $colClausure;
        public $link;


		public  $fields=null;


		function __construct($dao, $squema = null) {
            $this->dao = $dao;

            if($squema){
            	$this->squema = $squema;
            }else{
            	$this->squema =  Environment::getPath() .  "/views/common/list.php";
            }

			$this->title=false;
        }

		function setTitle($title){
			$this->title = $title;
		}


		function show(){

			$this->display($this->squema, get_object_vars($this));
		}

	}
