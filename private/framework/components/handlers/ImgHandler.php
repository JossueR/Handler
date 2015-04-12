<?php
    include( PATH_PRIVATE . "kernel.php");

	//loadClass("models/dao/ImgDAO.php");
	//loadClass("components/handlers/EndpointHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class ImgHandler extends Handler {
	private $reff_type;
	private $reff_id;
	private $type;
	private $name;
	private $callbackClass;
	private $callBackMethod;
	private $callbackClassname;
	private $default_value;
	
	public $squema_asociar;
	public $squema_form;
	
	const REFF_TYPE_CLIENT = "CLIENT";
	const ALLOW_TYPE_IMG = "IMG";
	const ALLOW_TYPE_DOCUMENT = "DOCUMENT";
	const ALLOW_TYPE_ALL = "ALL";
	
	public static $file_types_img = array(
		 "image/gif"  ,
		 "image/png"  ,
		 "image/jpeg" ,
		 "image/jpg"  ,
		 "image/JPEG" ,
		 "image/JPG"  ,
		 "image/PNG"  ,
		 "image/GIF"
	);
	
	public static $file_types_doc = array(
		  "image/GIF"
	);
	
	
	function __construct($name=null, $reff_type = null, $reff_id = null, $type = null) {
		$this->squema_asociar=PATH_FRAMEWORK . "views/common/asociarWorkspace.php";
		$this->squema_form=PATH_FRAMEWORK . "views/img/form.php";
		
		if(!$name){
			$name = $this->getRequestAttr('name');
		}	
			
		if(!$reff_type){
			$reff_type = $this->getRequestAttr('reff_type');
		}
		
		if(!$reff_id){
			$reff_id = $this->getRequestAttr('reff_id');
		}
		
		if(!$type){
			$type = $this->getRequestAttr('type');
		}
		
		$this->reff_type = $reff_type;
		$this->reff_id = $reff_id;
		$this->type = $type;
		$this->name = $name;
			
		$this->setVar("reff_type", $this->reff_type);
		$this->setVar("reff_id", $this->reff_id);
		$this->setVar("type", $this->type);
		$this->setVar("name", $this->name);
		
		$this->loadCallBack();
		$this->loadDefault();
    }
    
    private function loadCallBack(){
    	if(isset($_SESSION["ImgHandler"][$this->name])){
	    	$classname = $_SESSION["ImgHandler"][$this->name]["classname"];
			$classname .= $this->getHandlerSufix();
			$this->callBackMethod = $_SESSION["ImgHandler"][$this->name]["method"];
			$this->callbackClassname = $_SESSION["ImgHandler"][$this->name]["classname"];
			
			searchClass(PATH_HANDLERS, $classname);
		
			if (class_exists($classname)) {
				$my_class = new $classname();
				$this->callbackClass = $my_class;
			}
			
		}
		
    }
	
	private function loadDefault(){
		if(isset($_SESSION["ImgHandler"][$this->name])){
			if(isset($_SESSION["ImgHandler"][$this->name]["default"])){
				$this->default_value = $_SESSION["ImgHandler"][$this->name]["default"];
			}
		}
	}
    
    function setCallBack($classname, $method){
		$_SESSION["ImgHandler"][$this->name]["classname"] = $classname;
		$_SESSION["ImgHandler"][$this->name]["method"] = $method;
		
		$this->loadCallBack();
    }
	
	function setDefaultValue($value){
		$_SESSION["ImgHandler"][$this->name]["default"] = $value;
		
		$this->loadDefault();
	}
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
		$param = $this->getAllVars();
					
		$t = $this->listAction(false);
		
		$this->setVar("f", $t);
		$this->setVar("title", showMessage("images"));
		$this->setVar("name", "dash_assoc_img-" . $this->name);
		
		
		$param["do"] = "form";
		$this->setVar("link_assoc", Handler::asyncLoad("Img", "dash_assoc_img-" . $this->name, $param,true));
		
		$param["do"] = "list";
		$this->setVar("link_view",  Handler::asyncLoad("Img", "dash_assoc_img-" . $this->name, $param,true));
		$this->display($this->squema_asociar);
	}
	
	function listAction($show = true){
		$dao = new ImgDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		

		$dao->getActives($this->reff_type, $this->reff_id, "");
		

		TableGenerator::defaultOrder('createDate', true);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = "Img";
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		
		$tabla->setVar("reff_type", $this->reff_type);
		$tabla->setVar("reff_id", $this->reff_id);
		$tabla->setVar("type", $this->type);
		$tabla->setVar("name", $this->name);
		
		
		

		$tabla->fields="img_id,description";

		
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		$params = $this->getAllVars();
		$params['id'] = '#img_id#';
		$params['do'] = 'remove';
			
		$actions->addAction("", Handler::asyncLoad("Img", APP_HIDEN_CONTENT, $params, true, true, showMessage("confirm_inactivate", array("field" => "#description#"))), 
			array('class'=>'fa fa-trash-o  fa-lg fa-fw'));
		
		if(isset($this->callbackClass) && isset($this->callBackMethod)){
			$params['do'] = $this->callBackMethod;
			$actions->addAction("", Handler::asyncLoad($this->callbackClassname, APP_HIDEN_CONTENT, $params,true, true), 
				array('class'=>'fa fa-map-marker  fa-lg fa-fw'));	
		}
			
		//asocia las acciones
		$tabla->actions=$actions->getAllActions();
		
		//Labels
		$campos = $this->labelsDefinition();
		
		//set labels
		$tabla->legent = $campos->getRelation();
		
		$tabla->colClausure = function($row, $colName){
				if($colName == "img_id"){
					return array("data" => '<img src="Img?do=show&id='.$row[$colName].'" class="img-thumbnail def-thumbnail" />');
				}else{
					return array("data" =>$row[$colName]);
				}
			};
			
		$tabla->rowClausure = function($row){
			$html = array();
			
			if($this->default_value && $row["url"] == $this->default_value ){
				$html["class"] = "verde";
			}
			
			return $html;
		};
		
		if($show){
			$tabla->show();
		}
		
		return $tabla;
	}

	public function formAction($show = true){
		
		if($show){
			
			$this->display($this->squema_form);
		}
		
		//return $form;
	}
	
	private function storeImgFile($img_id){
		
		$filename = $_FILES['photo']['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		$target = PATH_UPLOAD . "upload/".$this->reff_type."/" . $this->reff_id . "_" . $img_id . "." . $ext;
		$error = false;
		
		$f_type=$_FILES['photo']['type'];
		
		switch ($this->type) {
			case self::ALLOW_TYPE_IMG:
				$haystack = self::$file_types_img;
			break;

			case self::ALLOW_TYPE_DOCUMENT:
				$haystack = self::$file_types_doc;
			break;
			
			default:
				$haystack = array_merge(self::$file_types_img, self::$file_types_doc);
			break;
		}
		
		if (!in_array($f_type, $haystack)){
				
				$error = true;
				$this->addError(showMessage("file_type"));
				$this->addError(showMessage($f_type));
		}else{
			
			if(!move_uploaded_file($_FILES['photo']['tmp_name'], $target)){
			 	$error = true;
			 }
		}
		
		 if($error){
		 	return null;
		 }else{
		 	return array("url" => $target, "content_type" => $f_type);
		 }
	}
	
	public function storeAction(){
		$img = new ImgDAO();
		
		$id = $this->getRequestAttr('id');
		
		$proto = $this->fillPrototype($img->getPrototype());
		

		
		//save
		if($img->save($proto)){
			$sumary = $img->getSumary();
			
			$data = $this->storeImgFile( $sumary->new_id);
			if(!is_null($data)){
				$proto['img_id'] = $sumary->new_id;
				$proto = array_merge($proto, $data);
				
				if($img->save($proto)){
							
					if(isset($this->callbackClass) && isset($this->callBackMethod) ){	
						$my_class = $this->callbackClass;
						$method = $this->callBackMethod;
						
						//echo var_dump($my_class);
						$my_class->$method($this->reff_id, $sumary->new_id, $proto["url"]);
						
						unset($_SESSION["ImgHandler"][$this->name]);
					}
					
					$this->windowReload(PATH_ROOT . "home");
				}else{
					$cols = $this->labelsDefinition();
					$this->addDbErrors($cols->getRelation(), $img->errors);
					$this->sendErrors();
				}
				
				
			}else{
				$this->addError(showMessage("file_error"));
				$this->sendErrors();
			}
			
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $img->errors);
			$this->sendErrors();
			
		}
	}

	/**
	 * 
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('img_id', showMessage('id'));
		$campos->addColumn('description', showMessage('description'));
		$campos->addColumn('url', showMessage('url'));

		return $campos;
	}
	
	public function inactivateAction(){
		$img = new ImgDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['img_id'] = $id;	
		}
		
		
		$proto['active']	=	ImgDAO::REG_DESACTIVADO_TX;
		
		//save
		if(!$this->haveErrors() && $img->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $img->errors);
			$this->sendErrors();
			
		}
	}
	
	public function showAction(){
		$id = $this->getRequestAttr("id", false);
		
		$dao = new ImgDAO();
		$dao->getById(array("img_id"=>$id));
		
		$img_info = $dao->get(); 
		
		if($img_info){
			header('Content-Type: ' . $img_info["content_type"]);
			$f = file_get_contents($img_info["url"]);
			
			echo $f;
		}
	}

	public function removeAction(){
		$id = $this->getRequestAttr('id');
		
		$dao = new ImgDAO();
		$dao->getById(array("img_id"=>$id));
		
		$img_info = $dao->get(); 
		
		if($dao->deleteByID(array("img_id" => $id))){
			unlink($img_info["url"]);
			
			Handler::asyncLoad("Img", "dash_assoc_img-" . $this->name, array(
				"do" => "list", 
				"reff_id" => $this->reff_id,
				"reff_type" => $this->reff_type,
				"type" => $this->type,
				"name" => $this->name
			));
		}else{
			$this->addError(showMessage($delete_img_error));
			$this->sendErrors();
		}
		
	}
	
	
	
	

	
}


?>