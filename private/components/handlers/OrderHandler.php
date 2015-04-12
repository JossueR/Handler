<?php
    include( PATH_PRIVATE . "kernel.php");

	loadClass("models/dao/OrderDAO.php");
	loadClass("models/dao/TableDAO.php");
	loadClass("models/dao/ProductDAO.php");
	
	loadClass("models/dao/OrderDetailDAO.php");
	loadClass("models/dao/ConfigVarDAO.php");
	
	//loadClass("components/handlers/EndpointHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class OrderHandler extends Handler {
	const TABLE_PREFIX = "order_prefix";
	const TABLE_LAST_SEC = "order_last_sec";
	
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
			
			$this->registerAction("Order", "<i class=\"fa fa-fw fa-gear\"></i>". showMessage("order"));
			$this->showTitle(showMessage("order"));
			
			$table_id = $this->getRequestAttr('id');
			
			$t = $this->listAction(false);
			$this->setVar("f", $t);
			$this->setVar("table_id", $table_id);
			$this->display("views/order/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listAction($show = true){
		$dao = new OrderDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		
		$dao->getActives();

		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		

		$tabla->fields="id,name,table_name,status";
		
		
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
							'do'=>'dash'
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
		$this->registerAction("orderForm", "orderForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$order = new OrderDAO();
		
		$id = $this->getRequestAttr('id');
		
		

		$order->getById(array("id"=>$id));
		$form->setVar('id', $id);
		
		$form->prototype = $order->getFilledPrototype();
		$form->name = "orderFrm";
		$form->action = "Order";
		$form->actionDO = "store";

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		

		if($show){
			$this->setVar("f", $form);
			$this->display("views/common/formWorkspace.php");
		}
		
		return $form;
	}

	public function newOrderAction(){
		$order = new OrderDAO();
		$table_id = $this->getRequestAttr('table_id');
		
		$proto = $order->getPrototype();
		$proto["name"]="";
		$proto["table_id"]=$table_id;

		//save
		if($order->save($proto)){
			$tableDAO = new TableDAO();
			$tableDAO->setLastOrderID($table_id, $order->getNewID());
				/*
				$this->asyncLoad('Order', "dash_order_detail", array(
					'do'=>'listOrderDetails',
					'order_id'=>$order->getNewID()
				));
				*/
				Handler::reloadLast(true);
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $order->errors);
			$this->sendErrors();
		}
	}
	
	public function storeAction($reloadList = true){
		$order = new OrderDAO();
		
		$id = $this->getRequestAttr('id');
		
		$proto = $this->fillPrototype($order->getPrototype());
		
		if($id){
			$proto['id'] = $id;	
		}
		
		#VALIDA NOMBRE UNICO
		if($order->validateName($proto['name'])){
			$this->addError(showMessage("duplicate_name"));
		}
		
		//save
		if(!$this->haveErrors() && $order->save($proto)){
			if($reloadList){
				$this->asyncLoad('Order', APP_CONTENT_BODY, array(
					'do'=>'listWorkspace',
					'objName'=>$tabla
				));
			}else{
				return true;
			}
			
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $order->errors);
			$this->sendErrors();
			
			if($reloadList){
				return false;	
			}
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
		$campos->addColumn('table_name', showMessage('table_name'));
		$campos->addColumn('status', showMessage('status'));
		$campos->addColumn('product_id', showMessage('product_id'));
		$campos->addColumn('product_name', showMessage('product'));
		$campos->addColumn('product_price', showMessage('price'));
		$campos->addColumn('cant', showMessage('cant'));
		$campos->addColumn('subtotal', showMessage('subtotal'));
		
		

		return $campos;
	}
	
	public function inactivateAction(){
		$order = new OrderDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['id'] = $id;	
		}
		
		
		$proto['active']	=	OrderDAO::REG_DESACTIVADO_TX;
		$proto['status']	=	OrderDAO::STATUS_CANCELED;
		
		//save
		if(!$this->haveErrors() && $order->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $order->errors);
			$this->sendErrors();
			
		}
	}
	
	public function reactivateAction(){
		$order = new OrderDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['id'] = $id;	
		}
		
		
		$proto['active']	=	OrderDAO::REG_ACTIVO_TX;
		
		//save
		if(!$this->haveErrors() && $order->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $order->errors);
			$this->sendErrors();
			
		}
	}
	
	function listInactivesWorkspaceAction(){
			$this->registerAction("orderInactives", showMessage("inactivos"));
			$this->showTitle(showMessage("order"));
			
			$t = $this->listInactivesAction(false);
			$this->setVar("f", $t);
			$this->display("views/order/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listInactivesAction($show = true){
		
		$dao = new OrderDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		$dao->getInactives();
		
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

	public function formConfigAction($show = true){
		$this->registerAction("orderConfigForm", "orderConfigForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$confDAO = new ConfigVarDAO();
		
		$form->prototype = array(
								"prefix" => $confDAO->getVar(self::TABLE_PREFIX),
								"seats" => null,
								"from" => $confDAO->getVar(self::TABLE_LAST_SEC),
								"to" => null
		);
		
		$form->name = "orderConfFrm";
		$form->action = "Order";
		$form->actionDO = "storeConfig";

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		

		if($show){
			$this->setVar("f", $form);
			$this->display("views/common/formWorkspace.php");
		}
		
		return $form;
	}
	
	public function storeConfigAction(){

		
		$proto = $this->fillPrototype(array(
								"prefix" => null,
								"seats" => null,
								"from" => null,
								"to" => null
		));
		
		$cols = $this->labelsDefinition();
		$label = $cols->getRelation();
		
		
		#VALIDA tipo dato
		if(!is_numeric($proto['seats'])  ){
			$this->addError(showMessage("field_no_int", array("field"=> $label['seats'])));
		}
		
		if(!is_numeric($proto['from'])  ){
			$this->addError(showMessage("field_no_int", array("field"=> $label['from'])));
		}
		
		
		if(!is_numeric($proto['to']) ){
			$this->addError(showMessage("field_no_int", array("field"=> $label['to'])));
		}
		
		$proto['from'] = intval($proto['from']);
		$proto['to'] = intval($proto['to']);
		$proto['seats'] = intval($proto['seats']);
		
		#valida to mayor a from
		if($proto['to'] <= $proto['from']){
			$this->addError(showMessage("to_less_than_from"));
		}
		
		#valida max cant de creados
		if(($proto['to'] - $proto['from']) > 100){
			$this->addError(showMessage("max_generates", array("num"=> 100)));
		}
		
		//save
		if(!$this->haveErrors()){
			
			for ($i=$proto['from']; $i <= $proto['to']; $i++) {
				Handler::setRequestAttr("name", $proto['prefix'].$i );
				Handler::setRequestAttr("seat", $proto['seats'] );
				
				$reloadList = ($i == $proto['to']);
				if(!$this->storeAction($reloadList)){
					break;
				}
			}
			
			$confDAO = new ConfigVarDAO();
			$confDAO->setVar(self::TABLE_PREFIX, $proto['prefix']);
			$confDAO->setVar(self::TABLE_LAST_SEC, $i);
		}else{
			
			
			$this->sendErrors();
			
		}
	}

	function dashAction(){
		$this->registerAction("orderDetail", "orderDetail");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$id = $this->getRequestAttr("id");
		
		$dao = new OrderDAO();
		$dao->getById(array("id"=>$id));
		
		$order = $dao->get();
		$dao->resetPointer($dao->getSumary());
		
		$view = new DataViewer($dao);
		$view->fields="id,name,status";
		
		//Labels
		$campos = $this->labelsDefinition();
		$view->legent=$campos->getRelation();
		
		$view->html = array(
			'class' => 'general_view col-lg-12  '
		);
		
		$view->setTitle(showMessage("details"));
		
		//$this->setVar('view', $view);
		$view->show();
		
		/*
		if($order["type"] != OrderDAO::TYPE_GROUP){
			$this->dashAsocAction("orders");	
		}
		
		*/
		
		
		 
		 
	}
	
	function listOrderDetailsAction($show = true){
		$dao = new OrderDetailDAO();
		$order_id = $this->getRequestAttr('order_id');
		
		//$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		
		$dao->getByOrder($order_id);
		TableGenerator::defaultOrder('id', false);
		
		$tabla = new TableGenerator($dao);
		
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		$tabla->controls = array();

		$tabla->fields="product_id,product_name,status,product_price,cant,subtotal";
		
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//Labels
		$campos = $this->labelsDefinition();
		
		//set labels
		$tabla->legent = $campos->getRelation();
		
		$tabla->totalsClausure = function($totals, $row){
				
			if(!isset($totals["subtotal"])){
				$totals["subtotal"] = 0;
			}
			
			$totals["subtotal"] = $totals["subtotal"] + $row["subtotal"];
			
			return $totals;
		};
		
		//crea las acciones
		$actions = new TableAcctions();
		
		$actions->addAction("", Handler::asyncLoad("Order", 'dash_order_detail', array(
					'order_id'=>'#order_id#',
					'id'=>'#id#',
					'do'=>'delProduct'
				),true, true, showMessage("confirmUnlink", array("field"=> "#product_name#"))), array('class'=>'fa fa-times-circle fa-lg fa-fw rojo'));
				
		//asocia las acciones
		$tabla->actions=$actions->getAllActions();
		
		if($show){
			$tabla->show();
		}
		
		return $tabla;
	}
	
	function dashOrderAction($orderID){
		$dao = new OrderDAO();
		$dao->getById(array("id"=>$orderID));
		$order = $dao->get();
		
		
		
		if($order["status"] == OrderDAO::STATUS_INPROCESS || $order["status"] == OrderDAO::STATUS_PARCIAL_BILLED){
			$showNew = false;
		}else{
			$showNew = true;
		}
		
		$table_id = $this->getRequestAttr('id');
		
		$this->setRequestAttr("order_id", $orderID);
		
		
		$t= null;
		if($order){
			$t = $this->listOrderDetailsAction(false);
		}
		$this->setVar("f", $t);
		
		$this->setVar("table_id", $table_id);
		$this->setVar("order_id", $orderID);
		$this->setVar("showNew", $showNew);
		$this->setVar("name", "dash_order_detail");
		$this->setVar("title", showMessage("order_title",$order));
		
		$this->display("views/order/asociarWorkspace.php");
	}
	
	function addProductAction(){
		$dao = new OrderDetailDAO();
		$productDAO = new ProductDAO();
		
		
		$order_id = $this->getRequestAttr('order_id');
		$product_id = $this->getRequestAttr('product_id');
		$cant = $this->getRequestAttr('cant');
		
		$productDAO->getById(array("product_id" => $product_id));
		$pro = $productDAO->get();
		
		if(!$this->haveErrors() && $dao->save(array(
									"order_id"=>$order_id,
									"product_id"=>$product_id,
									"product_name"=>$pro["name"],
									"product_observation"=>"",
									"product_price"=>$pro["price"],
									"cant"=>$cant
		))){
			
			$this->listOrderDetailsAction();
			
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $dao->errors);
			$this->sendErrors();
			
			
		}
	}
	
	function delProductAction(){
		$dao = new OrderDetailDAO();
		$productDAO = new ProductDAO();
		
		
		$order_id = $this->getRequestAttr('order_id');
		$id = $this->getRequestAttr('id');
		
		
		if(!$this->haveErrors() && $dao->save(array(
									"id"=>$id,
									"status"=>OrderDetailDAO::STATUS_CANCELED
		))){
			
			$this->listOrderDetailsAction();
			
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $dao->errors);
			$this->sendErrors();
			
			
		}
	}
}


?>