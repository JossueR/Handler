<?php
    include( PATH_PRIVATE . "kernel.php");

	//loadClass("models/dao/MsgDAO.php");
	//loadClass("components/handlers/EndpointHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class MsgHandler extends Handler {
	public $squema_workspace;
	public $squema_form;
	
	function __construct(){
		$this->squema_form= PATH_FRAMEWORK. "views/common/formWorkspace.php";
		$this->squema_workspace= PATH_FRAMEWORK . "views/msg/listWorkspace.php";
	}
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
			$this->clearSteps();
			$this->registerAction("Msg", "<i class=\"fa fa-fw fa-envelope-o\"></i>". showMessage("msg"));
			$this->showTitle(showMessage("msg"));
			
			$t = $this->listAction(false);
			$this->setVar("f", $t);
			$this->display($this->squema_workspace);
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listAction($show = true){
		$dao = new MsgDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		

		$dao->getActives();
		

		TableGenerator::defaultOrder('msg_id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		

		$tabla->fields="msg_id,name,email,phone,comment,createDate";
		
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		

			
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#msg_id#',
				'do'=>'inactivate'
			),true, true, showMessage("confirm_inactivate", array("field" => "#name#"))), 
			array('class'=>'fa fa-trash-o  fa-lg fa-fw'));
			
			
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
		$this->registerAction("msgForm", "msgForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$msg = new MsgDAO();
		
		$id = $this->getRequestAttr('id');
		
		

		$msg->getById(array("msg_id"=>$id));
		$form->setVar('id', $id);
		
		$form->prototype = $msg->getFilledPrototype();
		$form->name = "msgFrm";
		$form->action = "Msg";
		$form->actionDO = "store";

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		

		if($show){
			$this->setVar("f", $form);
			$this->display($this->squema_form);
		}
		
		return $form;
	}
	
	public function storeAction(){
		$msg = new MsgDAO();
		
		$id = $this->getRequestAttr('id');
		
		$proto = $this->fillPrototype($msg->getPrototype());
		
		if($id){
			$proto['msg_id'] = $id;	
		}
		
		//save
		if(!$this->haveErrors() && $msg->save($proto)){
			$this->asyncLoad('Msg', APP_CONTENT_BODY, array(
				'do'=>'listWorkspace',
				'objName'=>$tabla
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $msg->errors);
			$this->sendErrors();
			
		}
	}

	/**
	 * 
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('msg_id', showMessage('id'));
		$campos->addColumn('name', showMessage('name'));
		$campos->addColumn('email', showMessage('email'));
		$campos->addColumn('phone', showMessage('phone'));
		$campos->addColumn('comment', showMessage('comment'));
		$campos->addColumn('createDate', showMessage('createDate'));

		return $campos;
	}
	
	public function inactivateAction(){
		$msg = new MsgDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['msg_id'] = $id;	
		}
		
		
		$proto['active']	=	MsgDAO::REG_DESACTIVADO_TX;
		
		//save
		if(!$this->haveErrors() && $msg->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $msg->errors);
			$this->sendErrors();
			
		}
	}
	
	public function reactivateAction(){
		$msg = new MsgDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['msg_id'] = $id;	
		}
		
		
		$proto['active']	=	MsgDAO::REG_ACTIVO_TX;
		
		//save
		if(!$this->haveErrors() && $msg->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $msg->errors);
			$this->sendErrors();
			
		}
	}
	
	function listInactivesWorkspaceAction(){
			$this->registerAction("msgInactives", showMessage("inactivos"));
			$this->showTitle(showMessage("msg"));
			
			$t = $this->listInactivesAction(false);
			$this->setVar("f", $t);
			$this->display($this->squema_workspace);
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listInactivesAction($show = true){
		
		$dao = new MsgDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		$dao->getInactives();
		
		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'listInactives';
		$tabla->html = array(
			'class' => 'general'
		);
		$tabla->fields="msg_id,name,email,phone,comment,createDate";
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#msg_id#',
				'do'=>'reactivate'
			),true, true, showMessage("confirm_reactivate", array("field" => "#name#"))), array('class'=>'fa fa-share-square fa-flip-horizontal fa-lg fa-fw verde'));

					
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

	
}


?>