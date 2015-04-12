<?php
    include( PATH_PRIVATE . "kernel.php");

	//loadClass("models/dao/PermissionsDAO.php");
	//loadClass("components/handlers/EndpointHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class PermissionHandler extends Handler {
	const LIST_BY_USER_SELECTED = "LIST_BY_USER_SELECTED";
	const LIST_BY_USER_NOSELECTED = "LIST_BY_USER_NOSELECTED";
	
	const LIST_BY_ROL_SELECTED = "LIST_BY_ROL_SELECTED";
	const LIST_BY_ROL_NOSELECTED = "LIST_BY_ROL_NOSELECTED";
	
	public $squema_workspace;
	public $squema_form;
	public $squema_assoc;
	
	function __construct(){
		$this->squema_form= PATH_FRAMEWORK. "views/common/formWorkspace.php";
		$this->squema_workspace= PATH_FRAMEWORK . "views/permissions/listWorkspace.php";
		$this->squema_assoc= PATH_FRAMEWORK. "views/common/asociarWorkspace.php";
	}
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
			
			$this->registerAction("Permission", "<i class=\"fa fa-fw fa-gear\"></i>". showMessage("permission"));
			$this->showTitle(showMessage("permission"));
			
			$t = $this->listAction(false);
			$this->setVar("f", $t);
			$this->display($this->squema_workspace);
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listAction($show = true){
		$dao = new PermissionsDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		
		$type = $this->getRequestAttr('type');
		$reff_id = $this->getRequestAttr('reff_id');
		
		switch($type){
			case self::LIST_BY_USER_SELECTED:
				$dao->getAsocToUser($reff_id);
			break;
				
			case self::LIST_BY_USER_NOSELECTED:
				$dao->getNotAsocToUser($reff_id);
			break;
			
			case self::LIST_BY_ROL_SELECTED:
				$dao->getAsocToRol($reff_id);
			break;
				
			case self::LIST_BY_ROL_NOSELECTED:
				$dao->getNotAsocToRol($reff_id);
			break;
				
			default:
				$dao->getActives();
		}
		
		

		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		

		$tabla->fields="permission,description";
		
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		
		switch($type){
			case self::LIST_BY_USER_SELECTED:
				#Para des asociar a usuario
				$actions->addAction("", Handler::asyncLoad("Permission", APP_HIDEN_CONTENT, array(
					'permission'=>'#permission#',
					'user_id'=>$reff_id,
					'do'=>'unAssocToUser'
				),true, true, showMessage("confirmUnlink", array("field"=> "#permission#"))), array('class'=>'fa fa-times-circle fa-lg fa-fw rojo'));
			break;
				
			case self::LIST_BY_USER_NOSELECTED:
				#Para asociar a usuario
				$actions->addAction("", Handler::asyncLoad("Permission", APP_HIDEN_CONTENT, array(
					'permission'=>'#permission#',
					'user_id'=>$reff_id,
					'do'=>'assocToUser'
				),true), array('class'=>'fa fa-sign-in fa-lg fa-fw'));
				
				$tabla->setVar("reff_id", $reff_id);
			break;
			
			case self::LIST_BY_ROL_SELECTED:
				#Para des asociar a usuario
				$actions->addAction("", Handler::asyncLoad("Permission", APP_HIDEN_CONTENT, array(
					'permission'=>'#permission#',
					'rol_id'=>$reff_id,
					'do'=>'unAssocToRol'
				),true, true, showMessage("confirmUnlink", array("field"=> "#permission#"))), array('class'=>'fa fa-times-circle fa-lg fa-fw rojo'));
			break;
				
			case self::LIST_BY_ROL_NOSELECTED:
				#Para asociar a usuario
				$actions->addAction("", Handler::asyncLoad("Permission", APP_HIDEN_CONTENT, array(
					'permission'=>'#permission#',
					'rol_id'=>$reff_id,
					'do'=>'assocToRol'
				),true), array('class'=>'fa fa-sign-in fa-lg fa-fw'));
				
				$tabla->setVar("reff_id", $reff_id);
			break;
				
			default:
				$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
						'id'=>'#permission#',
						'do'=>'form'
					),true), array('class'=>'fa fa-pencil fa-lg fa-fw'));
		}
		
			
		
			
			
		//asocia las acciones
		$tabla->actions=$actions->getAllActions();
		
		//Labels
		$campos = $this->labelsDefinition();
		
		//set labels
		$tabla->legent = $campos->getRelation();
		
		if($show){
			$tabla->show();
		}
		
		return $tabla;
	}

	public function formAction($show = true){
		$this->registerAction("permissionForm", "permissionForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$permission = new PermissionsDAO();
		
		$id = $this->getRequestAttr('id');
		
		

		$permission->getById(array("permission"=>$id));
		$form->setVar('id', $id);
		
		$form->prototype = $permission->getFilledPrototype();
		$form->name = "permissionFrm";
		$form->action = "Permission";
		$form->actionDO = "store";
		
		$form->defineField(array(
				"campo"=>'description',
				"tipo" =>'textarea'
			));

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		

		if($show){
			$this->setVar("f", $form);
			$this->display($this->squema_form);
		}
		
		return $form;
	}
	
	public function storeAction(){
		$permission = new PermissionsDAO();
		
		$id = $this->getRequestAttr('id');
		
		$proto = $this->fillPrototype($permission->getPrototype());
		
		if($id){
			$proto['permission'] = $id;	
		}
		
		//save
		if(!$this->haveErrors() && $permission->save($proto)){
			$this->asyncLoad('Permission', APP_CONTENT_BODY, array(
				'do'=>'listWorkspace',
				'objName'=>$tabla
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $permission->errors);
			$this->sendErrors();
			
		}
	}

	/**
	 * 
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('permission', showMessage('permission'));
		$campos->addColumn('description', showMessage('description'));

		return $campos;
	}
	
	function dashAsocAction($title = "permissions", $selected = self::LIST_BY_USER_SELECTED, $notselected = self::LIST_BY_USER_NOSELECTED ){
		$reff_id = $this->getRequestAttr('id');
		$this->setRequestAttr("type", $selected);
		$this->setRequestAttr("reff_id", $reff_id);
		$t = $this->listAction(false);
		
		$this->setVar("f", $t);
		$this->setVar("title", showMessage($title));
		$this->setVar("name", "dash_assoc_per");
		
		$this->setVar("link_assoc", Handler::asyncLoad("Permission", "dash_assoc_per", array("do" => "list", "reff_id" => $reff_id, "type"=> $notselected),true));
		$this->setVar("link_view",  Handler::asyncLoad("Permission", "dash_assoc_per", array("do" => "list", "reff_id" => $reff_id, "type"=> $selected),true));
		$this->display($this->squema_assoc);
	}
	
	function assocToUserAction(){
		$user_id = $this->getRequestAttr('user_id');
		$permission = $this->getRequestAttr('permission');
		
		$dao = new PermissionsDAO();
		if($dao->addToUser($user_id, $permission)){
			Handler::asyncLoad("Permission", "dash_assoc_per", array(
				"do" => "list", 
				"reff_id" => $user_id,
				"type" => self::LIST_BY_USER_SELECTED
			));
		}else{
			$this->addError(showMessage("assocError"));
			$this->sendErrors();
		}
	}
	
	function unAssocToUserAction(){
		$user_id = $this->getRequestAttr('user_id');
		$permission = $this->getRequestAttr('permission');
		
		$dao = new PermissionsDAO();
		if($dao->delToUser($user_id, $permission)){
			Handler::asyncLoad("Permission", "dash_assoc_per", array(
				"do" => "list", 
				"reff_id" => $user_id,
				"type" => self::LIST_BY_USER_SELECTED
			));
		}else{
			$this->addError(showMessage("unassocError"));
			$this->sendErrors();
		}
	}
	
	function assocToRolAction(){
		$rol_id = $this->getRequestAttr('rol_id');
		$permission = $this->getRequestAttr('permission');
		
		$dao = new PermissionsDAO();
		if($dao->addToRol($rol_id, $permission)){
			Handler::asyncLoad("Permission", "dash_assoc_per", array(
				"do" => "list", 
				"reff_id" => $rol_id,
				"type" => self::LIST_BY_ROL_SELECTED
			));
		}else{
			$this->addError(showMessage("assocError"));
			$this->sendErrors();
		}
	}
	
	function unAssocToRolAction(){
		$rol_id = $this->getRequestAttr('rol_id');
		$permission = $this->getRequestAttr('permission');
		
		$dao = new PermissionsDAO();
		if($dao->delToRol($rol_id, $permission)){
			Handler::asyncLoad("Permission", "dash_assoc_per", array(
				"do" => "list", 
				"reff_id" => $rol_id,
				"type" => self::LIST_BY_ROL_SELECTED
			));
		}else{
			$this->addError(showMessage("unassocError"));
			$this->sendErrors();
		}
	}
	
}


?>