<?php
    include( PATH_PRIVATE . "kernel.php");

	loadClass("models/dao/CategoryDAO.php");
	loadClass("models/dao/ProductDAO.php");
	
	loadClass("components/handlers/TableHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class CategoryHandler extends Handler {
	
	function indexAction(){
		
		$this->listWorkspaceAction();
	}
	
	
	function listWorkspaceAction(){
			
			$this->registerAction("Category", "<i class=\"fa fa-fw fa-gear\"></i>". showMessage("category"));
			$this->showTitle(showMessage("category"));
			
			$t = $this->listAction(false);
			$this->setVar("f", $t);
			$this->display("views/category/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listAction($show = true){
		$dao = new CategoryDAO();
		$parent_id = $this->getRequestAttr('parent_id');
		$product_id= $this->getRequestAttr('product_id');
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		
		if(!is_null($parent_id)){
			$dao->getNotAssocToCategory($parent_id);
		}else if(!is_null($product_id)){
			$dao->getNotAssocToProduct($product_id);
		}else{
			$dao->getActives();
		}
		
		

		TableGenerator::defaultOrder('category_id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = "Category";
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'general'
		);
		

		$tabla->fields="category_id,name";
		
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		
		if(!is_null($parent_id)){
			$actions->addAction("", Handler::asyncLoad("Category", APP_HIDEN_CONTENT, array(
					'child_id'=>'#category_id#',
					'parent_id'=>$parent_id,
					'do'=>'assocToCategory'
				),true), array('class'=>'fa fa-sign-in fa-lg fa-fw'));
				
			$tabla->setVar("parent_id", $parent_id);
		}else if(!is_null($product_id)){
			$actions->addAction("", Handler::asyncLoad("Category", APP_HIDEN_CONTENT, array(
					'category_id'=>'#category_id#',
					'product_id'=>$product_id,
					'do'=>'assocToProduct'
				),true), array('class'=>'fa fa-sign-in fa-lg fa-fw'));
				
			$tabla->setVar("product_id", $product_id);
		}else{
			$actions->addAction("", Handler::asyncLoad("Category", APP_CONTENT_BODY, array(
					'id'=>'#category_id#',
					'do'=>'form'
				),true), array('class'=>'fa fa-pencil fa-lg fa-fw'));
				
			$actions->addAction("", Handler::asyncLoad("Category", APP_CONTENT_BODY, array(
					'id'=>'#category_id#',
					'do'=>'inactivate'
				),true, true, showMessage("confirm_inactivate", array("field" => "#name#"))), 
				array('class'=>'fa fa-trash-o  fa-lg fa-fw'));
				
			$actions->addAction("", Handler::asyncLoad("Category", APP_CONTENT_BODY, array(
						'id'=>'#category_id#',
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
		$this->registerAction("categoryForm", "categoryForm");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$form = new FormMaker();
		$category = new CategoryDAO();
		
		$id = $this->getRequestAttr('id');
		$parent_id = $this->getRequestAttr('parent_id');
		
		$category->getById(array("category_id"=>$id));
		$form->setVar('id', $id);
		
		if($parent_id){
			$form->setVar('parent_id', $parent_id);
		}
		
		$form->prototype = $category->getFilledPrototype();
		$form->name = "categoryFrm";
		$form->action = "Category";
		$form->actionDO = "store";

		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();
		
		$form->defineField(array(
				"campo"=>'is_root',
				"tipo" =>"select-array",
				"source"=>array(
					"1"=>"Y",
					"0"=>"N"
				)
			));
		

		if($show){
			
			if(!$parent_id){
				$this->setVar("f", $form);
				$this->display("views/common/formWorkspace.php");
			}else{
				$form->show();
			}
			
		}
		
		return $form;
	}
	
	public function storeAction(){
		$category = new CategoryDAO();
		
		$id = $this->getRequestAttr('id');
		$parent_id = $this->getRequestAttr('parent_id');
		
		$proto = $this->fillPrototype($category->getPrototype());
		
		if($id){
			$proto['category_id'] = $id;	
		}
		
		//save
		if(!$this->haveErrors() && $category->save($proto)){
			
			#si es categoria principal regresa a lista
			if(!$parent_id){
				$this->asyncLoad('Category', APP_CONTENT_BODY, array(
					'do'=>'listWorkspace',
					'objName'=>$tabla
				));
			}else{
				$this->setRequestAttr('child_id',$category->getNewID());
				$this->assocToCategoryAction();
			}
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $category->errors);
			$this->sendErrors();
			
		}
	}

	/**
	 * 
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('category_id', showMessage('id'));
		$campos->addColumn('name', showMessage('name'));
		$campos->addColumn('is_root', showMessage('is_root'));

		return $campos;
	}
	
	public function inactivateAction(){
		$category = new CategoryDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['category_id'] = $id;	
		}
		
		
		$proto['active']	=	CategoryDAO::REG_DESACTIVADO_TX;
		
		//save
		if(!$this->haveErrors() && $category->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $category->errors);
			$this->sendErrors();
			
		}
	}
	
	public function reactivateAction(){
		$category = new CategoryDAO();
	
		$id = $this->getRequestAttr('id');

		$proto = array();
		
		if($id){
			$proto['category_id'] = $id;	
		}
		
		
		$proto['active']	=	CategoryDAO::REG_ACTIVO_TX;
		
		//save
		if(!$this->haveErrors() && $category->save($proto)){
			$this->asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $category->errors);
			$this->sendErrors();
			
		}
	}
	
	function listInactivesWorkspaceAction(){
			$this->registerAction("categoryInactives", showMessage("inactivos"));
			$this->showTitle(showMessage("category"));
			
			$t = $this->listInactivesAction(false);
			$this->setVar("f", $t);
			$this->display("views/category/listWorkspace.php");
			Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	function listInactivesAction($show = true){
		
		$dao = new CategoryDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		$dao->getInactives();
		
		TableGenerator::defaultOrder('id', false);
		
		
		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'listInactives';
		$tabla->html = array(
			'class' => 'general'
		);
		$tabla->fields="category_id,name";
		
		$tabla->setName($this->getRequestAttr('objName'));
		
		//crea las acciones
		$actions = new TableAcctions();
		
		$actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
				'id'=>'#category_id#',
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

	function dashAction(){
		$this->registerAction("AddSubCategory", showMessage("AddSubCategory"));
		$this->showTitle(showMessage("AddSubCategory"));
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
		
		$id = $this->getRequestAttr('id');
		$dao = new CategoryDAO();
		
		$dao->getById(array("category_id"=>$id));
		$category = $dao->get();
		
		$dao->resetGetData();
		$tabla = new TableGenerator($dao);
		$tabla->fields="category_id,name";
		$tabla->html = array(
			'class' => 'general'
		);
		$tabla->controls=array();
		
		$this->setVar("f", $tabla);
		$this->display("views/common/sublistWorkspace.php");
		
		$this->dashAsocSubCatAction();
		
		$this->dashImagenAction();
		
	}

	function getListByCategoryAction($show = true){
		$category_id = $this->getRequestAttr('id');
		
		$dao = new CategoryDAO();
		$dao->getByCategory($category_id);
		
		$t = new ListGenerator($dao);
		$t->setShowField("name");
		$t->cancelLink = Handler::asyncLoad("Category", APP_HIDEN_CONTENT, array(
					'parent_id'=>$category_id,
					'child_id'=>'#category_id#',
					'do'=>'unAssocToCategory'
				),true, true, showMessage("confirmUnlink", array("field"=> "#name#")));
		
		if($show){
			$t->show();
		}
		
		return $t;
	}
	
	function dashAsocSubCatAction(){
		$category_id = $this->getRequestAttr('id');
		$t = $this->getListByCategoryAction(false);
		
		$this->setVar("f", $t);
		$this->setVar("title", showMessage("subcategories"));
		$this->setVar("name", "dash_assoc_subcat");
		
		$this->setVar("link_assoc", Handler::asyncLoad("Category", "dash_assoc_subcat", array("do" => "form", "parent_id" => $category_id),true));
		$this->setVar("link_view",  Handler::asyncLoad("Category", "dash_assoc_subcat", array("do" => "getListByCategory", "id" => $category_id),true));
		$this->display("views/common/asociarWorkspace.php");
	}
	
	function assocToCategoryAction(){
		$parent_id = $this->getRequestAttr('parent_id');
		$child_id = $this->getRequestAttr('child_id');
		
		$dao = new CategoryDAO();
		if($dao->addToCategory($parent_id, $child_id)){
			Handler::asyncLoad("Category", "dash_assoc_subcat", array(
				"do" => "getListByCategory", 
				"id" => $parent_id
			));
		}else{
			$this->addError(showMessage("assocError"));
			$this->sendErrors();
		}
	}
	
	function unAssocToCategoryAction(){
		$parent_id = $this->getRequestAttr('parent_id');
		$child_id = $this->getRequestAttr('child_id');
		
		$dao = new CategoryDAO();
		if($dao->delToCategory($parent_id, $child_id)){
			Handler::asyncLoad("Category", "dash_assoc_subcat", array(
				"do" => "getListByCategory", 
				"id" => $parent_id
			));
		}else{
			$this->addError(showMessage("unassocError"));
			$this->sendErrors();
		}
	}
	
	/////////
	
	function getListByProductAction($show = true){
		$product_id = $this->getRequestAttr('id');
		
		$dao = new CategoryDAO();
		$dao->getByProduct($product_id);
		
		$t = new ListGenerator($dao);
		$t->setShowField("name");
		$t->cancelLink = Handler::asyncLoad("Category", APP_HIDEN_CONTENT, array(
					'product_id'=>$product_id,
					'category_id'=>'#category_id#',
					'do'=>'unAssocToProduct'
				),true, true, showMessage("confirmUnlink", array("field"=> "#name#")));
		
		if($show){
			$t->show();
		}
		
		return $t;
	}
	
	function dashAsocProductAction(){
		$product_id = $this->getRequestAttr('id');
		$t = $this->getListByProductAction(false);
		
		$this->setVar("f", $t);
		$this->setVar("title", showMessage("categories"));
		$this->setVar("name", "dash_assoc_cat");
		
		$this->setVar("link_assoc", Handler::asyncLoad("Category", "dash_assoc_cat", array("do" => "list", "product_id" => $product_id),true));
		$this->setVar("link_view",  Handler::asyncLoad("Category", "dash_assoc_cat", array("do" => "getListByProduct", "id" => $product_id),true));
		$this->display("views/common/asociarWorkspace.php");
	}
	
	function assocToProductAction(){
		$product_id = $this->getRequestAttr('product_id');
		$category_id = $this->getRequestAttr('category_id');
		
		$dao = new CategoryDAO();
		if($dao->addToProduct($product_id, $category_id)){
			Handler::asyncLoad("Category", "dash_assoc_cat", array(
				"do" => "getListByProduct", 
				"id" => $product_id
			));
		}else{
			$this->addError(showMessage("assocError"));
			$this->sendErrors();
		}
	}
	
	function unAssocToProductAction(){
		$product_id = $this->getRequestAttr('product_id');
		$category_id = $this->getRequestAttr('category_id');
		
		$dao = new CategoryDAO();
		if($dao->delToProduct($product_id, $category_id)){
			Handler::asyncLoad("Category", "dash_assoc_cat", array(
				"do" => "getListByProduct", 
				"id" => $product_id
			));
		}else{
			$this->addError(showMessage("unassocError"));
			$this->sendErrors();
		}
	}
	
	function listImgAction($show = true){
		$id = $this->getRequestAttr('id');
		$dao = new CategoryDAO();
		
		$dao->getById(array("category_id"=>$id));

		$tabla = new TableGenerator($dao);
		$tabla->fields="category_id,image_url";
		$tabla->html = array(
			'class' => 'general'
		);
		$tabla->controls=array();
		
		$tabla->colClausure = function($row, $colName){
				if($colName == "image_url"){
					return array("data" => '<img src="Img?do=category&id='.$row["category_id"].'" class="img-thumbnail large-thumbnail" />');
				}else{
					return array("data" =>$row[$colName]);
				}
			};
		
		if($show){
			$tabla->show();
		}
		
		return $tabla;
	}
	
	
	
	function dashImagenAction(){

		
		$id = $this->getRequestAttr('id');
		
		$t = $this->listImgAction(false);
		
		$this->setVar("f", $t);
		$this->setVar("title", showMessage("images"));
		$this->setVar("name", "dash_assoc_img");
		
		$this->setVar("link_assoc", Handler::asyncLoad("Category", "dash_assoc_img", array("do" => "formImage", "id" => $id),true));
		$this->setVar("link_view",  Handler::asyncLoad("Category", "dash_assoc_img", array("do" => "listImg", "id" => $id),true));
		$this->display("views/common/asociarWorkspace.php");
	}
	
	public function formImageAction($show = true){
		$category_id = $this->getRequestAttr('id');
		
		if($show){
			$this->setVar('category_id', $category_id);
			$this->display("views/category/form.php");
		}
		
		//return $form;
	}
	
	private function storeImgFile($category_id){
		
		$filename = $_FILES['photo']['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		$target = PATH_UPLOAD . "upload/category/cat_" . $category_id . "." . $ext;
		$error = false;
		
		$f_type=$_FILES['photo']['type'];
		
		if ($f_type != "image/gif"  && 
			$f_type != "image/png"  && 
			$f_type != "image/jpeg" && 
			$f_type != "image/jpg"  && 
			$f_type != "image/JPEG" && 
			$f_type != "image/JPG"  && 
			$f_type != "image/PNG"  && 
			$f_type != "image/GIF"){
				
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
		 	return array("image_url" => $target, "content_type" => $f_type);
		 }
	}
	
	public function storeImgAction(){
		$category = new CategoryDAO();
		
		$id = $this->getRequestAttr('category_id');
		

		$data = $this->storeImgFile($id);
		
		if(!is_null($data)){
			$proto['category_id'] = $id;
			$proto = array_merge($proto, $data);
			
			if($category->save($proto)){
				$this->windowReload(PATH_ROOT . "home");
			}else{
				$cols = $this->labelsDefinition();
				$this->addDbErrors($cols->getRelation(), $category->errors);
				$this->sendErrors();
			}
			
			
		}else{
			$this->addError(showMessage("file_error"));
			$this->sendErrors();
		}
			
	}
	
	function categoryBlock($category){
		$cat = new CategoryDAO();
		$cat->getById(array("category_id"=>"1"));
		$category = $cat->get();
		
		$this->setVar("category", $category);
		$this->display("views/category/block.php");
	}
	
	function listBlockAction($show = true){
		$dao = new CategoryDAO();
		$parent_id = $this->getRequestAttr('parent_id');
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
		TableGenerator::removeOrder();
		TableGenerator::defaultOrder('category_id', false);
		
		if($parent_id){
			$dao->getByCategory($parent_id);
			$_SESSION["HISTORY_CATS"][] = $parent_id;
		}else{
			$dao->getRoots();
			$_SESSION["HISTORY_CATS"] = array();
		}
		
		
		while ($cat = $dao->get()) {
			$this->setVar("category", $cat);
			$this->display("views/category/block.php");
		}
	}
	
	function backCategoryAction(){
		$i =  count($_SESSION["HISTORY_CATS"]) -1;
		
		if($i > 0){
			array_pop($_SESSION["HISTORY_CATS"]);

			$this->setRequestAttr("parent_id", $_SESSION["HISTORY_CATS"][--$i]);
		}
		
		$h= new TableHandler();
		$h->showMenuAction();
		array_pop($_SESSION["HISTORY_CATS"]);
	}

	
}


?>