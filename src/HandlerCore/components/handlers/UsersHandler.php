<?php
namespace HandlerCore\components\handlers;
use HandlerCore\components\DashViewer;
use HandlerCore\components\DataViewer;
use HandlerCore\components\FormMaker;
use HandlerCore\components\Handler;
use HandlerCore\components\TableAcctions;
use HandlerCore\components\TableColumns;
use HandlerCore\components\TableGenerator;
use HandlerCore\Environment;
use HandlerCore\models\dao\AbstractBaseDAO;
use HandlerCore\models\dao\PermissionsDAO;
use HandlerCore\models\dao\RolDAO;
use HandlerCore\models\dao\UsersDAO;
use HandlerCore\models\SimpleDAO;

use function HandlerCore\showMessage;
/**
 *
 */
class UsersHandler extends Handler {
	const LIST_BY_ROL_SELECTED = "LIST_BY_ROL_SELECTED";
	const LIST_BY_ROL_NOSELECTED = "LIST_BY_ROL_NOSELECTED";
	const LIST_FOR_ASSIGN = "LIST_FOR_ASSIGN";
	const LIST_FOR_UNASSIGN = "LIST_FOR_UNASSIGN";
	const LIST_SHOW = "LIST_SHOW";
	const LIST_BY_DRIVERS = "LIST_BY_DRIVERS";

	const LIST_BY_CLIENT = "LIST_BY_CLIENT";
	const LIST_BY_CLIENT_NOT_SELECTED = "LIST_BY_CLIENT_NOT_SELECTED";

	public $squema_workspace;
	public $squema_form;
	public $squema_assoc;

	function __construct(){
		$this->squema_form= "views/common/formWorkspace.php";
		$this->squema_workspace= "views/users/listWorkspace.php";
		$this->squema_assoc= "views/common/asociarWorkspace.php";
	}

	function indexAction(){

		$this->listWorkspaceAction();
	}


	function listWorkspaceAction(){
			$this->clearSteps();
			$this->registerAction($this->getHandlerName(), "<i class=\"fa fa-fw fa-barcode\"></i>&nbsp;". showMessage($this->getHandlerName()));
			$this->showTitle(showMessage("UserMod"));

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
                    "link" => Handler::asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(

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

	}

	function listAction($show = true){
		$dao = new UsersDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE; $dao->disableExecFind();

		TableGenerator::defaultOrder('uid', false);

		$type = $this->getRequestAttr('type');
		$reff_id = $this->getRequestAttr('reff_id');

		switch($type){
			case self::LIST_SHOW:
				$id = $this->getRequestAttr('user_id');
				$dao->getById(array("uid"=>$id));
			break;

			case self::LIST_BY_ROL_SELECTED:

				$dao->getAsocToRol($reff_id);
			break;

			case self::LIST_BY_ROL_NOSELECTED:
				$dao->getNotAsocToRol($reff_id);
			break;

			case self::LIST_FOR_UNASSIGN:
			case self::LIST_FOR_ASSIGN:
				$data = $this->getRequestAttr('data');

				switch ($data) {
					case self::LIST_BY_DRIVERS;
						$dao->getByPermission("SHD-02");
					break;

					case self::LIST_BY_CLIENT;
						$client_id = $this->getRequestAttr('client_id');
						$dao->getByClient($client_id);
					break;

					case self::LIST_BY_CLIENT_NOT_SELECTED;
						$client_id = $this->getRequestAttr('client_id');
						$dao->getByClientNotSelected($client_id);
					break;

					default:
						$dao->getActives();

				}
			break;

			default:

				$dao->getActives();
		}





		$tabla = new TableGenerator($dao, __METHOD__);
		$tabla->reloadScript = "Users";
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'table table-striped'
		);

		switch($type){
			case self::LIST_SHOW:
				$tabla->pagin=false;
			case self::LIST_BY_ROL_SELECTED:
			case self::LIST_BY_ROL_NOSELECTED:
			case self::LIST_BY_CLIENT:
			case self::LIST_BY_CLIENT_NOT_SELECTED:
			case self::LIST_FOR_ASSIGN:
			case self::LIST_FOR_UNASSIGN:
				$tabla->fields="uid,username,nombre,apellidos";
			break;

			default:
				$tabla->fields="uid,username,nombre,apellidos,sexo,f_nacimiento";
		}

		$tabla->setVar("type", $type);
		$tabla->setName($this->getRequestAttr('objName'));

		//crea las acciones
		$actions = new TableAcctions();

		switch($type){
			case self::LIST_SHOW:
				#Para mostrar

			break;

			case self::LIST_BY_ROL_SELECTED:
				#Para des asociar a usuario
				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_HIDDEN_CONTENT, array(
					'rol_id'=>$reff_id,
					'reff_id'=>$reff_id,
					'user_id'=>'#uid#',
					'do'=>'unAssocToRol'
				),true, true, showMessage("confirmUnlink", array("field"=> "#username#"))), array('class'=>'fa fa-times-circle fa-lg fa-fw rojo'));
				$tabla->setVar("reff_id", $reff_id);
			break;

			case self::LIST_FOR_ASSIGN:
				$dest = $this->getRequestAttr('dest');
				$actionHandler = $this->getRequestAttr('actionHandler');
				$resend = $this->getRequestAttr('resend');


				#Para asociar a usuario
				$actions->addAction("", Handler::asyncLoad($actionHandler, $dest,$resend,true), array('class'=>'fas fa-sign-out-alt  fa-lg fa-fw'));


				$tabla->setVar("dest", $dest);
				$tabla->setVar("actionHandler", $actionHandler);
				$tabla->setVar("resend", $resend);
				$tabla->setVar("reff_id", $reff_id);
			break;

			case self::LIST_FOR_UNASSIGN:
				$dest = $this->getRequestAttr('dest');
				$actionHandler = $this->getRequestAttr('actionHandler');
				$resend = $this->getRequestAttr('resend');


				#Para asociar a usuario
				$actions->addAction("", Handler::asyncLoad($actionHandler, $dest,$resend,true, true, showMessage("confirmUnlink", array("field"=> "#username#"))), array('class'=>'fa fa-times-circle fa-lg fa-fw rojo'));

				$tabla->setVar("dest", $dest);
				$tabla->setVar("actionHandler", $actionHandler);
				$tabla->setVar("resend", $resend);
				$tabla->setVar("reff_id", $reff_id);
			break;

			case self::LIST_BY_ROL_NOSELECTED:
				#Para asociar a usuario
				$actions->addAction("", Handler::asyncLoad("Users", Environment::$APP_HIDDEN_CONTENT, array(
					'reff_id'=>$reff_id,
					'rol_id'=>$reff_id,
					'user_id'=>'#uid#',
					'do'=>'assocToRol'
				),true), array('class'=>'fas fa-sign-out-alt  fa-lg fa-fw'));

				$tabla->setVar("reff_id", $reff_id);
			break;

			default:
				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(
						'id'=>'#uid#',
						'do'=>'form'
					),true), array('class'=>'fa fa-pencil-alt fa-lg fa-fw fa-lg fa-fw'));

				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(
						'id'=>'#uid#',
						'do'=>'inactivate'
					),true, true, showMessage("confirm_inactivate", array("field"=> "#username#"))), array('class'=>'fa fa-trash-alt fa-lg fa-fw'));

				$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(
							'id'=>'#uid#',
							'do'=>'dash'
						),true), array('class'=>'fa fa-reply-all fa-rotate-180 fa-lg fa-fw'));
		}



		//asocia las acciones
		$tabla->actions=$actions->getAllActions();

		//Labels
		$campos = $this->labelsDefinition();

		//set labels
		$tabla->legent = $campos->getRelation();

		$tabla->colClausure = function($row, $colName){

				if($colName == "LDAP"){
					if($row[$colName] == UsersDAO::LOGIN_LDAP){
						$row[$colName] = "<i class='fa fa-share-alt'></i>";
					}else{
						$row[$colName] = "";
					}

				}

				return array("data" =>$row[$colName]);
			};

		if($show){
			$tabla->show();
		}

		return $tabla;
	}

	public function formAction($show = true){
		$this->registerAction("usersForm", "usersForm");

		$form = new FormMaker();
		$user = new UsersDAO();

		$id = $this->getRequestAttr('id');


		$user->getById(array("uid"=>$id));
		$form->setVar('id', $id);

		$form->prototype = $user->getFilledPrototype();
		$form->prototype["repass"] = "";
		$form->name = "userFrm";
		$form->action = "Users";
		$form->actionDO = "store";


		$form->defineField(array(
				"campo"=>'LDAP',
				"tipo" =>"select-array",
				"source"=>array(
					"0"=>"LOCAL",
					"1"=>"LDAP"
				)
			));

		$form->defineField(array(
				"campo"=>'sexo',
				"tipo" =>"select-array",
				"source"=>array(
					"M"=>"M",
					"F"=>"F"
				)
			));


		$form->defineField(array(
			"campo"=>'password',
			"tipo" =>"password"
		));

		$form->defineField(array(
			"campo"=>'repass',
			"tipo" =>"password"
		));

		$form->defineField(array(
			"campo"=>'f_nacimiento',
			"tipo" =>"date"
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
		$users = new UsersDAO();

		$id = $this->getRequestAttr('id');

		$proto = $this->fillPrototype($users->getPrototype());

		#variable para validar el password
		$check_pass = true;

		#Si es una edicion
		if($id){
			$proto['uid'] = $id;

			if(strlen($proto['password']) == 0){
				unset($proto['password']);
				unset($proto['repass']);

				$check_pass = false;
			}
		}
		//validate

		if(isset($proto["username"])){
			if($users->existBy($users->putQuoteAndNull(array("username"=>$proto["username"], "uid"=>AbstractBaseDAO::$SQL_TAG. " <> " . $id),!SimpleDAO::REMOVE_TAG))){
				$this->addError(showMessage("username_duplicate"));
			}

			#valida username
			if(strlen($proto['username']) < 4 ){
				$this->addError(showMessage("min_len", array("len"=>"4", "field"=>"Username")));
			}
		}

		#valida el password
		if($check_pass){
			if(isset($proto['LDAP']) && $proto['LDAP'] == UsersDAO::LOGIN_LDAP ){
				$proto['password'] = '000000';
			}else{
				$repass = $this->getRequestAttr('repass');

				if($proto['password'] != $repass){
					$this->addError(showMessage("pass_dont_match"));
				}

				if(strlen($proto['password']) < 7 ){
					$this->addError(showMessage("min_len", array("len"=>"7", "field"=>"Password")));
				}
			}


			$proto['password'] = UsersDAO::$SQL_TAG . "MD5('".$proto['password']."')";
		}



		//save
		if(!$this->haveErrors() && $users->save($proto)){
			$this->asyncLoad('Users', Environment::$APP_CONTENT_BODY, array(
				'do'=>'listWorkspace',

			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $users->errors);
			$this->sendErrors();

		}
	}

	/**
	 *
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('uid', showMessage('id'));
		$campos->addColumn('username', showMessage('username'));
		$campos->addColumn('repass', showMessage('repass'));
		$campos->addColumn('f_nacimiento', showMessage('f_nacimiento'));
		$campos->addColumn('nombre', showMessage('nombre'));
		$campos->addColumn('apellidos', showMessage('apellidos'));
		$campos->addColumn('sexo', showMessage('sexo'));
		$campos->addColumn('LDAP', 'LDAP');


		return $campos;
	}

	public function &selectActiveAction($show = true)
		{
			$dao = new UsersDAO();
			$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE; $dao->disableExecFind();
			$dao->getActives();

			$tabla = new TableGenerator($dao, __METHOD__);
			$tabla->reloadScript = Handler::$handler;
			$tabla->reloadDo = Handler::$do;
			$tabla->html = array(
				'class' => 'table table-striped'
			);
			$tabla->fields="uid,username";

			$tabla->setName($this->getRequestAttr('objName'));

			//Labels
			$campos = $this->labelsDefinition();


			//set labels
			$tabla->legent = $campos->getRelation();

			//crea las acciones
			$actions = new TableAcctions();
			$r = $this->getRequestAttr('returnField');
			$tabla->setVar('returnField', $r);

			$dialog = $this->getRequestAttr('dialog');
			$tabla->setVar('dialog', $dialog);

			$dest = $this->getRequestAttr('showDest');
			$tabla->setVar('showDest', $dest);

			$scri = $this->asyncLoad(Handler::$handler, $dest, array(
									"do"=>"show",
									"id"=>"#$r#"
									),true);
			$actions->addAction(showMessage('select'), "rvm_finder.select('$dialog','#$r#'); $scri", array('class'=>'go_icon'));


			//asocia las acciones
			$tabla->actions=$actions->getAllActions();


			if($show){
				$tabla->show();
			}

			return $tabla;
		}

	public function &showAction($show = true)
	{
		$dao = new UsersDAO();

		$dao->getById(array("uid"=>$this->getRequestAttr('id')));

		$tabla = new TableGenerator($dao, __METHOD__);
		$tabla->html = array(
			'class' => 'table table-striped'
		);
		$tabla->pagin = false;
		$tabla->fields = "uid,username";

		//Labels
		$campos = $this->labelsDefinition();

		//set labels
		$tabla->legent = $campos->getRelation();

		if($show){
			$tabla->show();
		}

		return $tabla;
	}

	public function inactivateAction(){
		$users = new UsersDAO();

		$id = $this->getRequestAttr('id');

		$proto = array();

		if($id){
			$proto['uid'] = $id;
		}


		$proto['active']	=	SimpleDAO::REG_DESACTIVADO;

		//save
		if(!$this->haveErrors() && $users->save($proto)){
			$this->asyncLoad(Handler::$handler, Environment::$APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $users->errors);
			$this->sendErrors();

		}
	}

	public function reactivateAction(){
		$users = new UsersDAO();

		$id = $this->getRequestAttr('id');

		$proto = array();

		if($id){
			$proto['uid'] = $id;
		}


		$proto['active']	=	UsersDAO::REG_ACTIVO;

		//save
		if(!$this->haveErrors() && $users->save($proto)){
			$this->asyncLoad(Handler::$handler, Environment::$APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $users->errors);
			$this->sendErrors();

		}
	}

	function listInactivesWorkspaceAction(){
			$this->registerAction("usersInactives", showMessage("inactivos"));
			$this->showTitle(showMessage("users"));

			$t = $this->listInactivesAction(false);
			$this->setVar("f", $t);
			$this->display($this->squema_workspace);
	}

	function listInactivesAction($show = true){

		$dao = new UsersDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE; $dao->disableExecFind();
		$dao->getInactives();

		TableGenerator::defaultOrder('uid', false);


		$tabla = new TableGenerator($dao, __METHOD__);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = 'listInactives';
		$tabla->html = array(
			'class' => 'table table-striped'
		);
		$tabla->fields="uid,username,LDAP";

		$tabla->setName($this->getRequestAttr('objName'));

		//crea las acciones
		$actions = new TableAcctions();

		$actions->addAction("", Handler::asyncLoad($this->getHandlerName(), Environment::$APP_CONTENT_BODY, array(
				'id'=>'#uid#',
				'do'=>'reactivate'
			),true, true, showMessage("confirm_reactivate", array("field" => "#nombre#"))), array('class'=>'fa fa-share-square fa-flip-horizontal fa-lg fa-fw verde'));


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

	public function changePassAction($show = true){

		if($show){
			$this->registerAction("changePassForm", "changePassForm");
		}
		$form = new FormMaker();
		$user = new UsersDAO();

		$id = $this->getRequestAttr('id');



		$form->setVar('id', $id);

		$form->prototype["password"] = "";
		$form->prototype["repass"] = "";
		$form->name = "userFrm";
		$form->action = "Users";
		$form->actionDO = "store";




		$form->defineField(array(
			"campo"=>'password',
			"tipo" =>"password"
		));

		$form->defineField(array(
			"campo"=>'repass',
			"tipo" =>"password"
		));


		$cols = $this->labelsDefinition();
		$form->legents = $cols->getRelation();


		if($show){
			$this->setVar("f", $form);
			$this->display($this->squema_form);
		}

		return $form;
	}

	function dashAction(){
		$this->registerAction("userDetail", "userDetail");


		$id = $this->getRequestAttr("id");

		$dao = new UsersDAO();
		$dao->getById(array("uid"=>$id));

		$view = new DataViewer($dao);
		$view->fields="uid,username,nombre,apellidos,sexo,f_nacimiento,LDAP";

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
		$h->dashAsocAction("user_permisions");

		$h = new RolHandler();
		$h->dashAsocAction("user_roles");


		echo "</div>";
	}

	function dashAsocAction($title = "users"){
		$reff_id = $this->getRequestAttr('id');
		$this->setRequestAttr("type", self::LIST_BY_ROL_SELECTED);
		$this->setRequestAttr("reff_id", $reff_id);
		TableGenerator::removeOrder();

		$t = $this->listAction(false);

		$this->setVar("f", $t);
		$this->setVar("title", showMessage($title));
		$this->setVar("name", "dash_assoc_users");

		$this->setVar("link_assoc", Handler::asyncLoad("Users", "dash_assoc_users", array("do" => "list", "reff_id" => $reff_id, "type"=> self::LIST_BY_ROL_NOSELECTED),true));
		$this->setVar("link_view",  Handler::asyncLoad("Users", "dash_assoc_users", array("do" => "list", "reff_id" => $reff_id, "type"=> self::LIST_BY_ROL_SELECTED),true));
		$this->display($this->squema_assoc);
	}

	function assocToRolAction(){
		$user_id = $this->getRequestAttr('user_id');
		$rol_id = $this->getRequestAttr('rol_id');

		$dao = new UsersDAO();
		if($dao->addToRol($user_id, $rol_id)){
			Handler::asyncLoad("Users", "dash_assoc_users", array(
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
		$user_id = $this->getRequestAttr('user_id');
		$rol_id = $this->getRequestAttr('rol_id');

		$dao = new UsersDAO();
		if($dao->delToRol($user_id, $rol_id)){
			Handler::asyncLoad("Users", "dash_assoc_users", array(
				"do" => "list",
				"reff_id" => $rol_id,
				"type" => self::LIST_BY_ROL_SELECTED
			));
		}else{
			$this->addError(showMessage("unassocError"));
			$this->sendErrors();
		}
	}


	/**
	 * @param $dao
	 * @param $reloadDo
	 * @return TableGenerator
	 */
	function &deffTable($dao, $reloadDo)
	{
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE; $dao->disableExecFind();

		TableGenerator::defaultOrder('uid', false);

		$tabla = new TableGenerator($dao, __METHOD__);
		$tabla->reloadScript = Handler::$handler;
		$tabla->reloadDo = $reloadDo;
		$tabla->html = array(
			'class' => 'table table-striped'
		);

		$tabla->fields="uid,username,nombre,apellidos,sexo,f_nacimiento,LDAP";

		$tabla->setName($this->getRequestAttr('objName'));

		//Labels
		$campos = $this->labelsDefinition();

		//set labels
		$tabla->legent = $campos->getRelation();

		return $tabla;
	}


	function listActivesForSelectAction($show = true){
		$dao = new UsersDAO();
		$tabla = $this->deffTable($dao, "listActivesForSelect");
		$dao->getActives();

		$tabla->fields = "uid,username,nombre,apellidos";
		$field = $this->getRequestAttr("field");

		//crea las acciones
		$actions = new TableAcctions();

		#Para asociar a usuario
		$actions->addAction("", Handler::loadValue("$field", "#uid#"), array('class'=>'fas fa-sign-out-alt  fa-lg fa-fw'));

		//asocia las acciones
		$tabla->actions=$actions->getAllActions();

		if($show){
			$tabla->show();
		}
	}
}


?>
