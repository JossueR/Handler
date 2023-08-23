<?php
    include( PATH_PRIVATE . "kernel.php");

	//loadClass("models/dao/RolDAO.php");
	loadClass(PATH_FRAMEWORK . "components/handlers/PermissionHandler.php");
	loadClass(PATH_FRAMEWORK . "components/handlers/UsersHandler.php");
	
	loadClass("components/handlers/MenuItemHandler.php");
	loadClass("components/handlers/SecAccessHandler.php");
	//loadClass("components/handlers/EndpointHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class RolHandler extends Handler {
	const LIST_BY_USER_SELECTED = "LIST_BY_USER_SELECTED";
	const LIST_BY_USER_NOSELECTED = "LIST_BY_USER_NOSELECTED";
	
	const PERMISSION_MASTER = "MASTER";
	
	public $squema_workspace;
	public $squema_form;
	public $squema_assoc;
	
	function __construct(){
		$this->squema_form= PATH_FRAMEWORK. "views/common/formWorkspace.php";
		$this->squema_workspace= PATH_FRAMEWORK . "views/rol/listWorkspace.php";
		$this->squema_assoc=  "views/common/asociarWorkspace.php";
	}
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
			
			$this->clearSteps();
			$this->registerAction($this->getHandlerName(), "<i class=\"fa fa-fw fa-barcode\"></i>&nbsp;". showMessage($this->getHandlerName()));
			$this->showTitle(showMessage("RolMod"));
			
		$dest = "ws";
        $dash = new DashViewer(__METHOD__);

        $dash->setVar("dest", $dest);
		
		$table = $this->listAction(false);
		
		$dash->setVar("f", $table);
		$dash->setVar("title", "");
		$dash->setVar("name", $dest);
		
		$dash->setVar("class_size", " col-lg-12 ");
		$dash->setVar("buttons", array(
			"add" => array(
                    "icon" => "fa-plus-circle",
                    "link" => Handler::asyncLoad($this->getHandlerName(), APP_CONTENT_BODY, array(
						
						'do'=>'form'
					),true),
                    "type" => "btn-lg btn-success"
                ),
			"all" => array(
                    "icon" => "fa-list",
                    "link" => Handler::asyncLoad($this->getHandlerName(), $dest, array(
						
						'do'=>'list'
					),true),
                    "type" => "btn-lg btn-primary"
                ),
			"inactives" => array(
                    "icon" => "fa-random",
                    "link" => Handler::asyncLoad($this->getHandlerName(), $dest, array(
						
						'do'=>'listInactives'
					),true),
                    "type" => "btn-lg btn-primary"
                ),
           	
		));
		
		echo "<div class='row'>";
		$dash->show();
		echo "</div>";
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listAction($show = true){
		$dao = new RolDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE; $dao->disableExecFind();
		
		$type = $this->getRequestAttr('type');
		$reff_id = $this->getRequestAttr('reff_id');
		
		$public = null;
		if(Handler::havePermission(self::PERMISSION_MASTER)){
			$public = SimpleDAO::REG_ACTIVO_Y;
		}

		switch($type){
			case self::LIST_BY_USER_SELECTED:
				$dao->getAsocToUser($reff_id);
			break;
				
			case self::LIST_BY_USER_NOSELECTED:
				$dao->getNotAsocToUser($reff_id,$public);
			break;
				
			default:
				if(Handler::havePermission(self::PERMISSION_MASTER)){
					$dao->getActives();
				}else{
					$dao->getPublicActives();
				}
		}
		

		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao, __METHOD__ . "::$type");
		$tabla->reloadScript = "Rol";
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'table table-striped'
		);
		

		$tabla->fields="id,name";
		
		
		$tabla->setName($this->getRequestAttr('objName'));
		$tabla->setVar("type", $type);
		
		//crea las acciones
		$actions = new TableAcctions();

		switch($type){
			case self::LIST_BY_USER_SELECTED:
				#Para des asociar a usuario
				$actions->addAction("", Handler::asyncLoad("Rol", APP_HIDEN_CONTENT, array(
					'rol_id'=>'#id#',
					'user_id'=>$reff_id,
					'do'=>'unAssocToUser'
				),true, true, showMessage("confirmUnlink", array("field"=> "#permission#"))), array('class'=>'fa fa-times-circle fa-lg fa-fw rojo'));
				$tabla->setVar("reff_id", $reff_id);
			break;
				
			case self::LIST_BY_USER_NOSELECTED:
				#Para asociar a usuario
				$actions->addAction("", Handler::asyncLoad("Rol", APP_HIDEN_CONTENT, array(
					'rol_id'=>'#id#',
					'user_id'=>$reff_id,
					'do'=>'assocToUser'
				),true), array('class'=>'fas fa-sign-out-alt  fa-lg fa-fw'));
				
				$tabla->setVar("reff_id", $reff_id);
			break;
				
			default:
				$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
						'id'=>'#id#',
						'do'=>'form'
					),true), array('class'=>'fas fa-pencil-alt-alt fa-lg fa-fw fa-lg fa-fw'));
					
				$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
					'id'=>'#id#',
					'do'=>'dash'
				),true), array('class'=>'fa fa-reply-all fa-rotate-180 fa-lg fa-fw'));
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
		$this->registerAction("rolForm", "rolForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$rol = new RolDAO();
		
		$id = $this->getRequestAttr('id');
		
		

		$rol->getById(array("id"=>$id));
		$form->setVar('id', $id);
		$proto = null;
		
		if(Handler::havePermission(self::PERMISSION_MASTER)){
			$proto = $rol->getPrototypeFull();
		}
		
		$form->prototype = $rol->getFilledPrototype($proto);
		$form->name = "rolFrm";
		$form->action = "Rol";
		$form->actionDO = "store";

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		
		$form->defineField(array(
			"campo"=>'public',
			"tipo" =>FormMaker::FIELD_TYPE_SELECT_ARRAY,
			"source"=>array(
				"Y"=>"Y",
				"N"=>"N"
			)
		));
		

		if($show){
			$this->setVar("f", $form);
			$this->display($this->squema_form);
		}
		
		return $form;
	}
	
	public function storeAction(){
		$rol = new RolDAO();
		
		$id = $this->getRequestAttr('id');
		
		if(Handler::havePermission(self::PERMISSION_MASTER)){
			$proto = $rol->getPrototypeFull();
		}else{
			$proto = $rol->getPrototype();
		}
		
		$proto = $this->fillPrototype($proto);
		
		if($id){
			$proto['id'] = $id;	
		}
		
		//save
		if(!$this->haveErrors() && $rol->save($proto)){
			$this->asyncLoad('Rol', APP_CONTENT_BODY, array(
				'do'=>'listWorkspace',
				'objName'=>$tabla
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $rol->errors);
			$this->sendErrors();
			
		}
	}

	/**
	 * 
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('id', showMessage('id'));
		$campos->addColumn('name', showMessage('name'));

		return $campos;
	}
	
	function dashAsocAction($title = "rols"){
		$reff_id = $this->getRequestAttr('id');
		$this->setRequestAttr("type", self::LIST_BY_USER_SELECTED);
		$this->setRequestAttr("reff_id", $reff_id);
		$t = $this->listAction(false);
		
		$this->setVar("f", $t);
		$this->setVar("title", showMessage($title));
		$this->setVar("name", "dash_assoc_rol");
		
		$this->setVar("link_assoc", Handler::asyncLoad("Rol", "dash_assoc_rol", array("do" => "list", "reff_id" => $reff_id, "type"=> self::LIST_BY_USER_NOSELECTED),true));
		$this->setVar("link_view",  Handler::asyncLoad("Rol", "dash_assoc_rol", array("do" => "list", "reff_id" => $reff_id, "type"=> self::LIST_BY_USER_SELECTED),true));
		$this->display($this->squema_assoc);
	}
	
	function assocToUserAction(){
		$user_id = $this->getRequestAttr('user_id');
		$rol_id = $this->getRequestAttr('rol_id');
		
		$dao = new RolDAO();
		if($dao->addToUser($user_id, $rol_id)){
			Handler::asyncLoad("Rol", "dash_assoc_rol", array(
				"do" => "list", 
				"reff_id" => $user_id,
				"type" => self::LIST_BY_USER_NOSELECTED
			));
		}else{
			$this->addError(showMessage("assocError"));
			$this->sendErrors();
		}
	}
	
	function unAssocToUserAction(){
		$user_id = $this->getRequestAttr('user_id');
		$rol_id = $this->getRequestAttr('rol_id');
		
		$dao = new RolDAO();
		if($dao->delToUser($user_id, $rol_id)){
			Handler::asyncLoad("Rol", "dash_assoc_rol", array(
				"do" => "list", 
				"reff_id" => $user_id,
				"type" => self::LIST_BY_USER_SELECTED
			));
		}else{
			$this->addError(showMessage("unassocError"));
			$this->sendErrors();
		}
	}
	
	function dashAction(){
		$this->registerAction("rolDetail", "rolDetail");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$id = $this->getRequestAttr("id");
		
		$dao = new RolDAO();
		$dao->getById(array("id"=>$id));
		
		$view = new DataViewer($dao);
		$view->fields="id,name";
		
		//Labels
		$campos = $this->labelsDefinition();
		$view->legent=$campos->getRelation();
		
		$view->html = array(
			'class' => 'general_view col-lg-12  '
		);
		
		$view->setTitle(showMessage("details"));
		
		//$this->setVar('view', $view);
		echo "<div class='row'>";
		$view->show();
		
		$h = new PermissionHandler();
		$h->dashAsocAction("permisions", PermissionHandler::LIST_BY_ROL_SELECTED, PermissionHandler::LIST_BY_ROL_NOSELECTED);
		
		$h = new UsersHandler();
		$h->dashAsocAction("users");
		
		$h = new MenuItemHandler();
		$h->dashListByRol($id);
		
		$h = new SecAccessHandler();
		$h->dashListByRol($id);
		 
		echo "</div>";
	}
	
	public function import($all, $force_delete=false, $start_transaction=true, $update=true){
		$status = false;
		
		$dao = new RolDAO();
		
		//si se pudo decodificar el json
		if($all){
			$dao->disableForeignKeyCheck();
			
			if($start_transaction){
				$dao->StartTransaction();
			}
			
			if($force_delete){
				$dao->truncate();
			}
			
			
			
			foreach ($all as $id => $proto) {
				
				//si no se quiere actualizar y la var existe
				if(!$update){
					$dao->getById($proto);
					$rol_data = $dao->get();
					//si el nombre del rol es distinto
					if($rol_data["id"] != "" &&  $proto["name"] != $rol_data["name"]){
						
						//elimina id para crear uno nuevo
						$proto["id"] = $dao->getPrefixedID();
					}
					
				}
				
				
				
				if($dao->save($proto)){
					
					
					$rol_id = $proto["id"];
					
					if(isset($proto["permission"]) && count($proto["permission"] > 0)){
						$permissionDAO = new PermissionsDAO();
						foreach ($proto["permission"] as $permission_data) {
							
							$permissionDAO->save($permission_data);
							
							//asocia a el rol
							$permissionDAO->addToRol($rol_id, $permission_data["permission"]);
						}
					}
				}else{
					$this->addDbErrors(array(), $dao->errors);
					break;
				}
			}
			
			//save
			if(!$this->haveErrors() ){
					
				$status = true;
				
				if($start_transaction){
					$dao->CommitTransaction();
				}
			}else{
				
				if($start_transaction){
					$dao->RollBackTransaction();
				}
			}
			$dao->enableForeignKeyCheck();
			
		}
		
		return $status;
	}
}


?>