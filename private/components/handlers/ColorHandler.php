<?php
    include( PATH_PRIVATE . "kernel.php");

	loadClass("models/dao/ColorDAO.php");
	//loadClass("components/handlers/EndpointHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class ColorHandler extends Handler {
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
			
			$this->registerAction("Color", "<i class=\"fa fa-fw fa-gear\"></i>". showMessage("color"));
			$this->showTitle(showMessage("color"));
			
			$t = $this->listAction(false);
			$this->setVar("f", $t);
			$this->display("views/color/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listAction($show = true){
		$dao = new ColorDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		

		$dao->getActives();
		

		TableGenerator::defaultOrder('color_id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		

		$tabla->fields="color_id,name";
		
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		

		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#color_id#',
				'do'=>'form'
			),true), array('class'=>'fa fa-pencil fa-lg fa-fw'));
			
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#color_id#',
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
		$this->registerAction("colorForm", "colorForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$color = new ColorDAO();
		
		$id = $this->getRequestAttr('id');
		
		

		$color->getById(array("color_id"=>$id));
		$form->setVar('id', $id);
		
		$form->prototype = $color->getFilledPrototype();
		$form->name = "colorFrm";
		$form->action = "Color";
		$form->actionDO = "store";

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		

		if($show){
			$this->setVar("f", $form);
			$this->display("views/common/formWorkspace.php");
		}
		
		return $form;
	}
	
	public function storeAction(){
		$color = new ColorDAO();
		
		$id = $this->getRequestAttr('id');
		
		$proto = $this->fillPrototype($color->getPrototype());
		
		if($id){
			$proto['color_id'] = $id;	
		}
		
		//save
		if(!$this->haveErrors() && $color->save($proto)){
			$this->asyncLoad('Color', APP_CONTENT_BODY, array(
				'do'=>'listWorkspace',
				'objName'=>$tabla
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $color->errors);
			$this->sendErrors();
			
		}
	}

	/**
	 * 
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('color_id', showMessage('id'));
		$campos->addColumn('name', showMessage('name'));

		return $campos;
	}
	
	public function inactivateAction(){
		$color = new ColorDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['color_id'] = $id;	
		}
		
		
		$proto['active']	=	ColorDAO::REG_DESACTIVADO_TX;
		
		//save
		if(!$this->haveErrors() && $color->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $color->errors);
			$this->sendErrors();
			
		}
	}
	
	public function reactivateAction(){
		$color = new ColorDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['color_id'] = $id;	
		}
		
		
		$proto['active']	=	ColorDAO::REG_ACTIVO_TX;
		
		//save
		if(!$this->haveErrors() && $color->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $color->errors);
			$this->sendErrors();
			
		}
	}
	
	function listInactivesWorkspaceAction(){
			$this->registerAction("colorInactives", showMessage("inactivos"));
			$this->showTitle(showMessage("color"));
			
			$t = $this->listInactivesAction(false);
			$this->setVar("f", $t);
			$this->display("views/color/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listInactivesAction($show = true){
		
		$dao = new ColorDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		$dao->getInactives();
		
		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'listInactives';
		$tabla->html = array(
			'class' => 'general'
		);
		$tabla->fields="color_id,name";
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#color_id#',
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