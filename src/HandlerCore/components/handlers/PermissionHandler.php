<?php
namespace HandlerCore\components\handlers;
use HandlerCore\components\DashViewer;
use HandlerCore\components\FormMaker;
use HandlerCore\components\Handler;
use HandlerCore\components\TableAcctions;
use HandlerCore\components\TableColumns;
use HandlerCore\components\TableGenerator;
use HandlerCore\Environment;
use HandlerCore\models\dao\PermissionsDAO;
use HandlerCore\models\SimpleDAO;
use function HandlerCore\showMessage;

/**
 *
 */
class PermissionHandler extends Handler {
	const LIST_BY_USER_SELECTED = "LIST_BY_USER_SELECTED";
	const LIST_BY_USER_NOSELECTED = "LIST_BY_USER_NOSELECTED";

	const LIST_BY_ROL_SELECTED = "LIST_BY_ROL_SELECTED";
	const LIST_BY_ROL_NOSELECTED = "LIST_BY_ROL_NOSELECTED";

	const PERMISSION_MASTER = "MASTER";

	protected $squema_workspace;
    protected $squema_form;
    protected $squema_assoc;

	function __construct(){
		$this->squema_form= "views/common/formWorkspace.php";
		$this->squema_workspace= "views/permissions/listWorkspace.php";
		$this->squema_assoc=  "views/common/asociarWorkspace.php";
	}

	function indexAction(){

		$this->listWorkspaceAction();
	}


	function listWorkspaceAction(){

			$this->registerAction("Permission", "<i class=\"fa fa-fw fas fa-cog\"></i>". showMessage("permission"));
			$this->showTitle(showMessage("permission"));

			$dest = "ws";
	        $dash = new DashViewer(__METHOD__);

	        $dash->setVar("dest", $dest);

			$table = $this->listAction(false);

			$dash->setVar("f", $table);
			$dash->setVar("title", "");
			$dash->setVar("name", $dest);

			$dash->setVar("class_size", " col-lg-12 ");
			$dash->setVar("buttons", array(
				"new" => array(
	                    "icon" => "fa-plus-circle",
	                    "link" => Handler::asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(
							'do'=>'form'
						),true),
	                    "type" => "btn-lg btn-success"
	                ),
	            "export" => array(
	                    "icon" => "fa-cloud-download",
	                    "link" => Handler::syncLoad($this->getHandlerName(), "", array(
							'do'=>'export'
						),true),
	                    "type" => "btn-lg btn-default"
	                ),
				"import" => array(
	                    "icon" => "fa-cloud-upload",
	                    "link" => Handler::asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(
							'do'=>'importFrm'
						),true),
	                    "type" => "btn-lg btn-default"
	                )


			));

			echo "<div class='row'>";
			$dash->show();
			echo "</div>";
	}

	function listAction($show = true){
		$dao = new PermissionsDAO();
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
				$dao->getNotAsocToUser($reff_id, $public);
			break;

			case self::LIST_BY_ROL_SELECTED:
				$dao->getAsocToRol($reff_id);
			break;

			case self::LIST_BY_ROL_NOSELECTED:
				$dao->getNotAsocToRol($reff_id, $public);
			break;

			default:
				if(Handler::havePermission(self::PERMISSION_MASTER)){
					$dao->getActives();
				}else{
					$dao->getPublicActives();
				}
		}



		TableGenerator::defaultOrder('id', false);


		$tabla = new TableGenerator($dao, __METHOD__);
		$tabla->reloadScript = "Permission";
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'table table-striped'
		);


		$tabla->fields="permission,description";


		$tabla->setName($this->getRequestAttr('objName'));
		$tabla->setVar("type", $type);

		//crea las acciones
		$actions = new TableAcctions();

		switch($type){
			case self::LIST_BY_USER_SELECTED:
				#Para des asociar a usuario
				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_HIDDEN_CONTENT, array(
					'permission'=>'#permission#',
					'user_id'=>$reff_id,
					'do'=>'unAssocToUser'
				),true, true, showMessage("confirmUnlink", array("field"=> "#permission#"))), array('class'=>'fa fa-times-circle fa-lg fa-fw rojo'));
				$tabla->setVar("reff_id", $reff_id);
			break;

			case self::LIST_BY_USER_NOSELECTED:
				#Para asociar a usuario
				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_HIDDEN_CONTENT, array(
					'permission'=>'#permission#',
					'user_id'=>$reff_id,
					'do'=>'assocToUser'
				),true), array('class'=>'fas fa-sign-out-alt  fa-lg fa-fw'));

				$tabla->setVar("reff_id", $reff_id);
			break;

			case self::LIST_BY_ROL_SELECTED:
				#Para des asociar a usuario
				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_HIDDEN_CONTENT, array(
					'permission'=>'#permission#',
					'rol_id'=>$reff_id,
					'do'=>'unAssocToRol'
				),true, true, showMessage("confirmUnlink", array("field"=> "#permission#"))), array('class'=>'fa fa-times-circle fa-lg fa-fw rojo'));
				$tabla->setVar("reff_id", $reff_id);
			break;

			case self::LIST_BY_ROL_NOSELECTED:
				#Para asociar a usuario
				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_HIDDEN_CONTENT, array(
					'permission'=>'#permission#',
					'rol_id'=>$reff_id,
					'do'=>'assocToRol'
				),true), array('class'=>'fas fa-sign-out-alt  fa-lg fa-fw'));

				$tabla->setVar("reff_id", $reff_id);
			break;

			default:
				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(
						'id'=>'#permission#',
						'do'=>'form'
					),true), array('class'=>'fas fa-pencil-alt-alt fa-lg fa-fw fa-lg fa-fw'));
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


		$form = new FormMaker();
		$permission = new PermissionsDAO();

		$id = $this->getRequestAttr('id');



		$permission->getById(array("permission"=>$id));
		$form->setVar('id', $id);
		$proto = null;
		if(Handler::havePermission(self::PERMISSION_MASTER)){
			$proto = $permission->getPrototypeFull();
		}

		$form->prototype = $permission->getFilledPrototype($proto);
		$form->name = "permissionFrm";
		$form->action = $this->getHandlerName();
		$form->actionDO = "store";

		$form->defineField(array(
				"campo"=>'description',
				"tipo" =>'textarea'
			));

		$form->defineField(array(
			"campo"=>'public',
			"tipo" =>FormMaker::FIELD_TYPE_SELECT_ARRAY,
			"source"=>array(
				"Y"=>"Y",
				"N"=>"N"
			)
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

		if(Handler::havePermission(self::PERMISSION_MASTER)){
			$proto = $permission->getPrototypeFull();
		}else{
			$proto = $permission->getPrototype();
		}

		$proto = $this->fillPrototype($proto);

		if($id){
			$proto['permission'] = $id;
		}

		//save
		if(!$this->haveErrors() && $permission->save($proto)){
			$this->asyncLoad('Permission', Environment::$APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
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
	public function &labelsDefinition(): TableColumns
    {
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

		$this->setVar("link_assoc", Handler::asyncLoad($this->getHandlerName(), "dash_assoc_per", array("do" => "list", "reff_id" => $reff_id, "type"=> $notselected),true));
		$this->setVar("link_view",  Handler::asyncLoad($this->getHandlerName(), "dash_assoc_per", array("do" => "list", "reff_id" => $reff_id, "type"=> $selected),true));
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
				"type" => self::LIST_BY_USER_NOSELECTED
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
			Handler::asyncLoad($this->getHandlerName(), "dash_assoc_per", array(
				"do" => "list",
				"reff_id" => $rol_id,
				"type" => self::LIST_BY_ROL_NOSELECTED
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

	public function exportAction(){
		$dao = new PermissionsDAO();
		$dao->getActives();
		$dao->escaoeHTML_OFF();
		$data = $dao->fetchAll();
		$dao->escaoeHTML_ON();

		header("Content-Type: text/plain");
		echo json_encode($data);
	}

	public function importFrmAction($show = true){
		$form = new FormMaker();




		$form->prototype = array(
			"data" => null
		);
		$form->name = "confFrmImp";
		$form->action = $this->getHandlerName();
		$form->actionDO = "import";


		$form->defineField(array(
					"campo"=>'data',
					"tipo" =>FormMaker::FIELD_TYPE_TEXTAREA,
				));


		if($show){


				$this->setVar("f", $form);
				$this->display("views/common/formWorkspace.php");


		}

		return $form;
	}

	public function importAction(){


		$json= $this->getRequestAttr('data');

		$all = json_decode($json, true);

		//si se pudo decodificar el json
		if($this->import($all)){

			//save
			if(!$this->haveErrors() ){

				#
				$this->asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(
					'do'=>'listWorkspace'
				));

			}else{

				$this->sendErrors();

			}

		}

	}

	public function import($all, $force_delete=false, $start_transaction=true){
		$status = false;

		$dao = new PermissionsDAO();

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


				if(!$this->haveErrors() && $dao->save($proto)){

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
