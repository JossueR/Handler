<?php
    include( PATH_PRIVATE . "kernel.php");

	loadClass("models/dao/ProductDAO.php");

	
	loadClass("components/handlers/ImgHandler.php");
	loadClass("components/handlers/CategoryHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class ProductHandler extends Handler {
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
			$this->clearSteps();
			$this->registerAction("Product", "<i class=\"fa fa-fw fa-gear\"></i>". showMessage("product"));
			$this->showTitle(showMessage("product"));
			
			$t = $this->listAction(false);
			$this->setVar("f", $t);
			$this->display("views/product/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listAction($show = true){
		$dao = new ProductDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		

		$dao->getActives();
		

		TableGenerator::defaultOrder('product_id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		

		$tabla->fields="product_id,name,categorias,createDate,createUser,updateDate,updateUser";
		
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		

		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#product_id#',
				'do'=>'form'
			),true), array('class'=>'fa fa-pencil fa-lg fa-fw'));
			
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#product_id#',
				'do'=>'inactivate'
			),true, true, showMessage("confirm_inactivate", array("field" => "#name#"))), 
			array('class'=>'fa fa-trash-o  fa-lg fa-fw'));
		
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
					'id'=>'#product_id#',
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
		$this->registerAction("productForm", "productForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$product = new ProductDAO();
		
		$id = $this->getRequestAttr('id');
		
		

		$product->getById(array("product_id"=>$id));
		$form->setVar('id', $id);
		
		$form->prototype = $product->getFilledPrototype();
		$form->name = "productFrm";
		$form->action = "Product";
		$form->actionDO = "store";
		
		$form->defineField(array(
				"campo"=>'description',
				"tipo" =>'textarea'
			));
			
		
		
		 
		$form->defineField(array(
				"campo"=>'published',
				"tipo" =>"select-array",
				"source"=>array(
					"ACTIVE"=>"Y",
					"INACTIVE"=>"N"
				)
			));

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		

		if($show){
			$this->setVar("f", $form);
			$this->display("views/common/formWorkspace.php");
		}
		
		return $form;
	}
	
	public function storeAction(){
		$product = new ProductDAO();
		
		$id = $this->getRequestAttr('id');
		
		$proto = $this->fillPrototype($product->getPrototype());
		
		if($id){
			$proto['product_id'] = $id;	
		}
		
		//save
		if(!$this->haveErrors() && $product->save($proto)){
			
			if(!$id){
				$id = $product->getNewID();	
			}
			
			$this->asyncLoad('Product', APP_CONTENT_BODY, array(
				'do'=>'details',
				'id'=>$id
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $product->errors);
			$this->sendErrors();
			
		}
	}

	/**
	 * 
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('product_id', showMessage('id'));
		$campos->addColumn('name', showMessage('name'));
		$campos->addColumn('brand_name', showMessage('brand'));
		$campos->addColumn('type_name', showMessage('type'));
		$campos->addColumn('quality_name', showMessage('quality'));
		$campos->addColumn('brand_id', showMessage('brand'));
		$campos->addColumn('type_id', showMessage('type'));
		$campos->addColumn('quality_id', showMessage('quality'));
		$campos->addColumn('published', showMessage('published'));
		$campos->addColumn('createDate', showMessage('createDate'));
		$campos->addColumn('createUser', showMessage('createUser'));
		$campos->addColumn('updateDate', showMessage('updateDate'));
		$campos->addColumn('updateUser', showMessage('updateUser'));
		$campos->addColumn('price', showMessage('price'));
		$campos->addColumn('color_name', showMessage('color'));
		$campos->addColumn('color_id', showMessage('color'));
		$campos->addColumn('categorias', showMessage('category'));

		return $campos;
	}
	
	public function inactivateAction(){
		$product = new ProductDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['product_id'] = $id;	
		}
		
		
		$proto['active']	=	ProductDAO::REG_DESACTIVADO_TX;
		
		//save
		if(!$this->haveErrors() && $product->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $product->errors);
			$this->sendErrors();
			
		}
	}
	
	public function reactivateAction(){
		$product = new ProductDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['product_id'] = $id;	
		}
		
		
		$proto['active']	=	ProductDAO::REG_ACTIVO_TX;
		
		//save
		if(!$this->haveErrors() && $product->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $product->errors);
			$this->sendErrors();
			
		}
	}
	
	function listInactivesWorkspaceAction(){
			$this->registerAction("productInactives", showMessage("inactivos"));
			$this->showTitle(showMessage("product"));
			
			$t = $this->listInactivesAction(false);
			$this->setVar("f", $t);
			$this->display("views/product/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listInactivesAction($show = true){
		
		$dao = new ProductDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		$dao->getInactives();
		
		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'listInactives';
		$tabla->html = array(
			'class' => 'general'
		);
		$tabla->fields="product_id,name,brand_name,quality_name,color_name,type_name,createDate,createUser,updateDate,updateUser";
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#product_id#',
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
		$this->registerAction("producDetail", "producDetail");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$id = $this->getRequestAttr("id");
		
		$dao = new ProductDAO();
		$dao->getById(array("product_id"=>$id));
		
		$view = new DataViewer($dao);
		$view->fields="product_id,name,description,upc,price,published,createUser,createDate,updateUser,updateDate";
		
		//Labels
		$campos = $this->labelsDefinition();
		$view->legent=$campos->getRelation();
		
		$view->html = array(
			'class' => 'general_view col-lg-12  '
		);
		
		$view->setTitle(showMessage("details"));
		
		//$this->setVar('view', $view);
		$view->show();
		
		$h = new ImgHandler();
		$h->listWorkspaceAction();
		
		$h = new CategoryHandler();
		$h->dashAsocProductAction();
	}
	
	function setDefImgAction(){
		$img_id = $this->getRequestAttr('img_id');
		$product_id = $this->getRequestAttr('product_id');
		$product = new ProductDAO();
		
		$proto["default_img"] = $img_id;
		$proto["product_id"] = $product_id;
		
		//save
		if(!$this->haveErrors() && $product->save($proto)){
			Handler::asyncLoad("Img", "dash_assoc_img", array(
				"do" => "list", 
				"id" => $product_id
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $product->errors);
			$this->sendErrors();
			
		}
	}

	function listBlockAction($show = true){
		$dao = new ProductDAO();
		$category_id = $this->getRequestAttr('parent_id');
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		TableGenerator::removeOrder();
		TableGenerator::defaultOrder('name', false);
		
		if($category_id){
			$dao->getByCategory($category_id);
			
		}else{
			$dao->getRoots();
			
		}
		
		
		while ($item = $dao->get()) {
			$this->setVar("item", $item);
			$this->display("views/product/block.php");
		}
	}
}


?>