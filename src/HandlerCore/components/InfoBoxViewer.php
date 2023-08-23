<?php
namespace HandlerCore\components;


	class InfoBoxViewer extends Handler {
		private $squema;
		public  $title;
		public  $html;
		public $icon;
        public $cant;
        public $subtitle;
        public $link;
        private $type;
        private  $name;
		public $class;
		private $counter;
		private $scripts;
		public $bar_percent;


        const CLASSTYPE_INFO = "card-info";
        const CLASSTYPE_WARNING = "card-warning";
        const CLASSTYPE_DANGER = "card-danger";
        const CLASSTYPE_SUCCESS = "card-success";

		const TYPE_DEFAULT = "infoBox.php";
		const TYPE_WITH_BACKGROUND = "infoBox_with_bg.php";
		const TYPE_WITH_BAR = "infoBox_with_bar.php";


		public  $fields=null;


		function __construct($name, $type=null) {
			switch ($type) {
				case self::TYPE_WITH_BACKGROUND :
				case self::TYPE_WITH_BAR :
					$this->type = $type;
				break;

				default:
					$this->type = self::TYPE_DEFAULT;
				break;
			}

            $this->name = $name;
            $this->squema = "views/common/" . $this->type;
            $this->scripts = array();

			$this->title=false;
        }




		function show(){

			$this->display($this->squema, get_object_vars($this));

			$this->finalScripts();
		}

		function setCounter($status){
			$this->counter = ($status == true);
		}

		function addScript($script){
			$this->scripts[] = $script;
		}


		private function finalScripts(){
			foreach ($this->scripts as $script) {
                echo "<script>$script</script>";

			}
		}


	}
