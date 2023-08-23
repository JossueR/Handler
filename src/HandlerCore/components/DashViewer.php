<?php
namespace HandlerCore\components;

	/**
	 *
	 */
	class DashViewer extends Handler {
		const BTN_ICON = "icon";
		const BTN_LINK = "link";
		const BTN_TYPE = "type";

		private $squema;
		//referencia de donde fue invokado
		private $invoker;

		private $title;
		private $name;
        public  $icon_class;
		public  $html = array();
		//arreglo con los nombre que se mostraran
		public  $legent=array();

		public  $fields=null;
		private $only_show_content;

		protected $postSripts;


		function __construct($inkoker = null, $squema = null) {
			$this->invoker = $inkoker;

            if($squema){
            	$this->squema = $squema;
            }else{
            	$this->squema = "views/common/asociarWorkspace.php";
            }

			$this->title=false;
			$this->only_show_content = false;

			$this->setVar("body_class", "table-responsive");
        }

		function setTitle($title){
			$this->title = $title;
		}


		function show(){
			//carga el nombre si no esta
			$this->loadName();


			$sec = new DynamicSecurityAccess();
			if($sec->checkDash($this->invoker, $this->name)){
				if($this->only_show_content){
					$f = $this->getVar("f");
					$content = $this->getVar("content");
					$script = $this->getVar("script");
					$script_params = $this->getVar("script_params");

					if($f){
						$f->show();
					}else if($content){
						echo $content;
					}else if($script){
						$this->display($script,$script_params);
					}

				}else{
					$this->display($this->squema, get_object_vars($this));
				}

			}
		}

		private function  loadName(){
			if(!$this->name){
				$this->name = $this->getVar("name");
			}
		}

		public function addPostScript($script, $have_script_tag=false){
			if(!$have_script_tag){
				$script = "<script>" . $script . "</script>";
			}

			$this->postSripts[] = $script;
		}

		public function OnlyShowContent(){
			$this->only_show_content = true;
		}

	}

