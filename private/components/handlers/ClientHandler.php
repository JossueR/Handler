<?php
    include( PATH_PRIVATE . "kernel.php");

	loadClass("models/dao/ClientDAO.php");
	loadClass("models/dao/ImgDAO.php");
	
	loadClass("components/handlers/ImgHandler.php");
	//loadClass("components/handlers/EndpointHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class ClientHandler extends Handler {
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
			$this->clearSteps();
			$this->registerAction("Cliente", "<i class=\"fa fa-fw fa-gear\"></i>". showMessage("passients"));
			$this->showTitle(showMessage("passients"));
			
			$t = $this->listAction(false);
			$this->setVar("f", $t);
			$this->display("views/client/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listAction($show = true){
		$dao = new ClientDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		

		$dao->getPassientsActives();
		

		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		

		$tabla->fields="id,name,lastname,sex";
		
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		

		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#id#',
				'do'=>'form'
			),true), array('class'=>'fa fa-pencil fa-lg fa-fw'));
			
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#id#',
				'do'=>'inactivate'
			),true, true, showMessage("confirm_inactivate", array("field" => "#name#"))), 
			array('class'=>'fa fa-trash-o  fa-lg fa-fw'));
			
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
					'id'=>'#id#',
					'do'=>'details'
				),true), array('class'=>'fa fa-reply-all fa-rotate-180 fa-lg fa-fw'));	
				
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
		$this->registerAction("clientForm", "clientForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$client = new ClientDAO();
		
		$id = $this->getRequestAttr('id');
		
		

		$client->getById(array("id"=>$id));
		$form->setVar('id', $id);
		
		$form->prototype = $client->getFilledPrototype();
		$form->name = "clientFrm";
		$form->action = "Client";
		$form->actionDO = "store";

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		
		$form->defineField(array(
				"campo"=>'sex',
				"tipo" =>"select-array",
				"source"=>array(
					"M"=>"M",
					"F"=>"F"
				)
			));
			
		$form->defineField(array(
				"campo"=>'is_emp',
				"tipo" =>"select-array",
				"source"=>array(
					"Y"=>"Y",
					"N"=>"N"
				)
			));
			
		$form->defineField(array(
				"campo"=>'is_passient',
				"tipo" =>"select-array",
				"source"=>array(
					"Y"=>"Y",
					"N"=>"N"
				)
			));
			
		$form->defineField(array(
				"campo"=>'adress',
				"tipo" =>"textarea"
			));
			
		$form->defineField(array(
				"campo"=>'birthday',
				"tipo" =>"date"
			));

		if($show){
			$this->setVar("f", $form);
			$this->display("views/common/formWorkspace.php");
		}
		
		return $form;
	}
	
	public function storeAction(){
		$client = new ClientDAO();
		
		$id = $this->getRequestAttr('id');
		
		$proto = $this->fillPrototype($client->getPrototype());
		
		if($id){
			$proto['id'] = $id;	
		}
		
		//save
		if(!$this->haveErrors() && $client->save($proto)){
			
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'details',
				'id'=>$id
			));
			
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $client->errors);
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
		$campos->addColumn('lastname', showMessage('lastname'));
		$campos->addColumn('sex', showMessage('sex'));

		return $campos;
	}
	
	public function inactivateAction(){
		$client = new ClientDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['id'] = $id;	
		}
		
		
		$proto['active']	=	ClientDAO::REG_DESACTIVADO_TX;
		
		//save
		if(!$this->haveErrors() && $client->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $client->errors);
			$this->sendErrors();
			
		}
	}
	
	public function reactivateAction(){
		$client = new ClientDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['id'] = $id;	
		}
		
		
		$proto['active']	=	ClientDAO::REG_ACTIVO_TX;
		
		//save
		if(!$this->haveErrors() && $client->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $client->errors);
			$this->sendErrors();
			
		}
	}
	
	function listInactivesWorkspaceAction(){
			$this->registerAction("clientInactives", showMessage("inactivos"));
			$this->showTitle(showMessage("client"));
			
			$t = $this->listInactivesAction(false);
			$this->setVar("f", $t);
			$this->display("views/client/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listInactivesAction($show = true){
		
		$dao = new ClientDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		$dao->getPassientsInactives();
		
		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'listInactives';
		$tabla->html = array(
			'class' => 'general'
		);
		$tabla->fields="id,name";
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#id#',
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

	function detailsAction(){
		$this->registerAction("clientDetail", "clientDetail");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$id = $this->getRequestAttr("id");
		
		$dao = new ClientDAO();
		$dao->getById(array("id"=>$id));
		
		$client = $dao->get();
		$dao->resetGetData();
		
		$view = new DataViewer($dao);
		$view->fields="id,DNI,name,lastname,birthday,sex,country,city,adress,active";
		
		//Labels
		$campos = $this->labelsDefinition();
		$view->legent=$campos->getRelation();
		
		$view->html = array(
			'class' => 'general_view col-lg-12  '
		);
		
		$view->setTitle("<i class=\"fa fa-th-list fa-lg fa-fw\"></i>" . showMessage("details"));
		
		//$this->setVar('view', $view);
		$view->show();
		
		
		
		$h = new ImgHandler("clientPhoto_$id",ImgHandler::REFF_TYPE_CLIENT,$id, ImgHandler::ALLOW_TYPE_IMG);
		$h->setCallBack("Client", "updateIMG");
		$h->setDefaultValue($client["img_url"]);
		$h->listWorkspaceAction();
	}
	
	function updateIMGAction(){
		
		$id = $this->getRequestAttr("reff_id");
		$img_id = $this->getRequestAttr("id");
		$url = $this->getRequestAttr("url");
		
		$dao  = new ImgDAO();
		$dao->getById(array("img_id" => $img_id));
		$img = $dao->get();
		
		
		
		$this->updateIMG($id,$img_id, $img["url"], true);
	}
	
	function updateIMG($id=null, $img_id=null, $url=null, $reload=false){
var_dump($url);
		$dao = new ClientDAO();
		//echo var_dump("in client");
		if($dao->save(array(
			"id" => $id,
			"img_url" => $url
		))){
			
			if($reload){
				$this->windowReload(PATH_ROOT . APP_DEFAULT_HANDLER);
			}
			
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $dao->errors);
			$this->sendErrors();
			
		}
		
	}
}


?>