<?php
namespace HandlerCore\components\handlers;
use HandlerCore\components\Handler;
use HandlerCore\components\TableAcctions;
use HandlerCore\components\TableColumns;
use HandlerCore\components\TableGenerator;
use HandlerCore\Environment;
use HandlerCore\models\dao\ImgDAO;
use HandlerCore\models\SimpleDAO;
use function HandlerCore\showMessage;

/**
 *
 */
class ImgHandler extends Handler {

    private $reff_type;
	private $reff_id;
	private $type;
	private $name;
	private $callbackClass;
	private $callBackMethod;
	private $callbackClassname;
	private $default_value;
	private $idInName;
	private $file_name_mode;
	private $last_insert_id;
	private $is_attachment;

	public $squema_asociar;
	public $squema_form;
	public $file_field_name;
	public $path_upload;

	public $enableAssocBtn=true;
	public $enableListBtn=true;
	public $enableRemoveBtn=true;


	const REFF_TYPE_CLIENT = "CLIENT";
	const ALLOW_TYPE_URL_IMG = "URL_IMG";
	const ALLOW_TYPE_RAW_IMG = "RAW_IMG";
	const ALLOW_TYPE_IMG = "IMG";
	const ALLOW_TYPE_DOCUMENT = "DOCUMENT";
	const ALLOW_TYPE_ALL = "ALL";

	const MODE_NAME_REFF_AND_ID = "MODE_REFF_AND_ID";
	const MODE_NAME_REFF_ID = "MODE_REFF_ID";

	public static $file_types_img = array(
		 "image/gif"  ,
		 "image/png"  ,
		 "image/jpeg" ,
		 "image/jpg"  ,
		 "image/JPEG" ,
		 "image/JPG"  ,
		 "image/PNG"  ,
		 "image/GIF" ,
		 "video/3gpp"
	);

	public static $file_types_doc = array(
		  "image/GIF",
		  "multipart/form-data",
		  "application/pdf",
		  "application/msword",
		  "text/plain",
		  "application/excel",
		  "application/vnd.ms-excel",
		  "application/vnd.ms-excel",
		  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
		  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
		  "application/vnd.openxmlformats-officedocument.presentationml.presentation"
	);

    private static string $generalSchema = "";
    private static string $generalFormSchema = "";


	function __construct($name=null, $reff_type = null, $reff_id = null, $type = null) {
		$this->squema_asociar= Environment::getPath() . "views/common/asociarWorkspace.php";
		$this->squema_form= Environment::getPath() . "views/img/form.php";

        if(self::$generalSchema != ""){
            $this->squema_asociar = self::$generalSchema;
        }else{
            $this->squema_asociar = Environment::getPath() .  "/views/common/asociarWorkspace.php";
            $this->usePrivatePathInView=false;
        }

        if(self::$generalFormSchema != ""){
            $this->squema_form = self::$generalFormSchema;
        }else{
            $this->squema_form = Environment::getPath() . "views/img/form.php";
            $this->usePrivatePathInView=false;
        }

		$this->file_field_name = 'photo';
		$this->path_upload = Environment::$PATH_UPLOAD;
		$this->file_name_mode = self::MODE_NAME_REFF_AND_ID;
		$this->is_attachment = false;

		if(!$name){
			$name = $this->getRequestAttr('name');
		}else{
			unset($_SESSION["ImgHandler"][$name]);
		}

		if(!$reff_type){
			$reff_type = $this->getRequestAttr('reff_type');
		}

		if(!$reff_id){
			$reff_id = $this->getRequestAttr('reff_id');
		}

		if(!$type){
			$type = $this->getRequestAttr('type');
		}

		$this->reff_type = $reff_type;
		$this->reff_id = $reff_id;
		$this->type = $type;
		$this->name = $name;

		$this->setVar("reff_type", $this->reff_type);
		$this->setVar("reff_id", $this->reff_id);
		$this->setVar("type", $this->type);
		$this->setVar("name", $this->name);

		$this->loadCallBack();
		$this->loadDefault();


		$this->checkFolder();
    }

    public static function setGeneralSchema(string $generalSchema): void
    {
        self::$generalSchema = $generalSchema;
    }

    public static function setGeneralFormSchema(string $generalFormSchema): void
    {
        self::$generalFormSchema = $generalFormSchema;
    }





    private function checkFolder(){
    	if (!file_exists($this->path_upload . $this->reff_type)) {
		    mkdir($this->path_upload . $this->reff_type, 0777);
		}
    }

    private function loadCallBack(){
    	if(isset($_SESSION["ImgHandler"][$this->name])){
	    	$classname = $_SESSION["ImgHandler"][$this->name]["classname"];
			$classname .= $this->getHandlerSufix();
			$this->callBackMethod = $_SESSION["ImgHandler"][$this->name]["method"];
			$this->callbackClassname = $_SESSION["ImgHandler"][$this->name]["classname"];

            if (!class_exists($classname)) {
                $classname = Environment::$NAMESPACE_HANDLERS .  $classname;
            }

			if (class_exists( $classname)) {
				$my_class = new $classname();
				$this->callbackClass = $my_class;
			}

		}

    }

	private function loadDefault(){
		if(isset($_SESSION["ImgHandler"][$this->name])){
			if(isset($_SESSION["ImgHandler"][$this->name]["default"])){
				$this->default_value = $_SESSION["ImgHandler"][$this->name]["default"];
			}
		}
	}

    function setCallBack($classname, $method){
		$_SESSION["ImgHandler"][$this->name]["classname"] = $classname;
		$_SESSION["ImgHandler"][$this->name]["method"] = $method;

		$this->loadCallBack();
    }

	function setDefaultValue($value){
		$_SESSION["ImgHandler"][$this->name]["default"] = $value;

		$this->loadDefault();
	}

	function indexAction(){

		$this->listWorkspaceAction();
	}


	function listWorkspaceAction($title = null, $show = true){
		if(!$title){
			$title = showMessage("documents");
		}

		$param = $this->getAllVars();

		$t = $this->listAction(false);

		$this->setVar("f", $t);
		$this->setVar("title", $title);
		$this->setVar("name", "dash_assoc_img-" . $this->name);

		if($this->enableAssocBtn){
			$param["do"] = "form";
			$this->setVar("link_assoc", Handler::asyncLoad("Img", "dash_assoc_img-" . $this->name, $param,true));
		}

		if($this->enableListBtn){
			$param["do"] = "list";
			$this->setVar("link_view",  Handler::asyncLoad("Img", "dash_assoc_img-" . $this->name, $param,true));
		}

		if($show){
			$this->display($this->squema_asociar);
		}

	}

	function show(){
		$this->display($this->squema_asociar);
	}

	function listAction($show = true){
		$dao = new ImgDAO();
		$dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;


		$dao->getActives($this->reff_type, $this->reff_id, "");


		TableGenerator::defaultOrder('createDate', true);


		$tabla = new TableGenerator($dao);
		$tabla->reloadScript = "Img";
		$tabla->reloadDo = 'list';
		$tabla->html = array(
			'class' => 'table table-striped'
		);

		$tabla->setVar("reff_type", $this->reff_type);
		$tabla->setVar("reff_id", $this->reff_id);
		$tabla->setVar("type", $this->type);
		$tabla->setVar("name", $this->name);




		$tabla->fields="img_id,description";



		$tabla->setName($this->getRequestAttr('objName'));

		//crea las acciones
		$actions = new TableAcctions();
		$params = $this->getAllVars();
		$params['id'] = '#img_id#';
		$params['do'] = 'remove';

		if($this->enableRemoveBtn){
			$actions->addAction("", Handler::asyncLoad("Img", Environment::$APP_HIDDEN_CONTENT, $params, true, true, showMessage("confirm_inactivate", array("field" => "#description#"))),
			array('class'=>'fa fa-trash-alt fa-lg fa-fw'));
		}


		if(isset($this->callbackClass) && isset($this->callBackMethod)){
			$params['do'] = $this->callBackMethod;
			$actions->addAction("", Handler::asyncLoad($this->callbackClassname, Environment::$APP_HIDDEN_CONTENT, $params,true, true),
				array('class'=>'fa fa-map-marker  fa-lg fa-fw'));
		}

		//asocia las acciones
		$tabla->actions=$actions->getAllActions();

		//Labels
		$campos = $this->labelsDefinition();

		//set labels
		$tabla->legent = $campos->getRelation();

		$tabla->colClausure = function($row, $colName){
				if($colName == "img_id"){



						$filename = $row["filename"];
						if($row["filename"] === ""){
							$filename = $row["description"];
						}

						return array("data" => '<a href="javascript:void(0)" onclick="'.$this->syncLoad($this->getHandlerName(), '', array(
							"do"=>"show",
							"id"=>$row["img_id"],
							"attach"=>"y"
						),true).'">'.$filename.'</a>');





				}else if($colName == "description"){

					if(in_array($row["content_type"],  self::$file_types_img)){
						return array("data" => '<img src="Img?do=show&id='.$row["img_id"].'" class="img-thumbnail def-thumbnail" width="70px"/>');
					}else{
						return array("data" =>$row[$colName]);
					}

				}else{
					return array("data" =>$row[$colName]);
				}


			};

		$dv = $this->default_value;
		$tabla->rowClausure = function($row) use (&$dv){
			$html = array();

			if($dv && $row["img_id"] == $dv ){
				$html["class"] = " alert-success ";
			}

			return $html;
		};

		if($show){
			$tabla->show();
		}

		return $tabla;
	}

	public function formAction($show = true){

		if($show){

			$this->display($this->squema_form);
		}

		//return $form;
	}

	public function validate(){
		$status = true;

		$filename = $_FILES[$this->file_field_name]['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$f_type=$_FILES[$this->file_field_name]['type'];

		switch ($this->type) {
			case self::ALLOW_TYPE_IMG:
				$haystack = self::$file_types_img;
			break;

			case self::ALLOW_TYPE_DOCUMENT:
				$haystack = self::$file_types_doc;
			break;

			default:
				$haystack = array_merge(self::$file_types_img, self::$file_types_doc);
			break;
		}

		if (!in_array($f_type, $haystack)){

				$status = false;
				$this->addError(showMessage("file_type"));
				$this->addError(showMessage($f_type));
		}

		return $status;
	}

	private function storeImgFile($img_id){

		$filename = $_FILES[$this->file_field_name]['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		$new_filename = $this->makeFileName($this->reff_id, $img_id, $ext);
		$target = $this->path_upload  . $this->reff_type."/" . $new_filename;
		$error = false;

		$f_type=$_FILES[$this->file_field_name]['type'];
		if($f_type == "multipart/form-data"){
			$f_type = mime_content_type($_FILES[$this->file_field_name]['tmp_name']);
		}

		switch ($this->type) {
			case self::ALLOW_TYPE_IMG:
				$haystack = self::$file_types_img;
			break;

			case self::ALLOW_TYPE_DOCUMENT:
				$haystack = self::$file_types_doc;
			break;

			default:
				$haystack = array_merge(self::$file_types_img, self::$file_types_doc);
			break;
		}

		if (!in_array($f_type, $haystack)){

				$error = true;
				$this->addError(showMessage("file_type"));
				$this->addError(showMessage($f_type));
		}else{

			if(!move_uploaded_file($_FILES[$this->file_field_name]['tmp_name'], $target)){
			 	$error = true;
				$this->addError(showMessage("cant_move_image"));
			 }
		}

		 if($error){
		 	return null;
		 }else{
		 	return array("url" => $target,
		 				"content_type" => $f_type,
						"filename" => $filename,
						"ext" => $ext);
		 }
	}

	private function storeRawImgFile($img_id){

		$filename = $this->name . ".png";
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		$new_filename = $this->makeFileName($this->reff_id, $img_id, $ext);
		$target = $this->path_upload  . $this->reff_type."/" . $new_filename;
		$error = false;

		$f_type="image/PNG";


		$data_uri = $this->getRequestAttr($this->name);
		$encoded_image = explode(",", $data_uri);
		$decoded_image = base64_decode($encoded_image[1]);
        file_put_contents($target ,$decoded_image);




	 	return array("url" => $target,
	 				"content_type" => $f_type,
					"filename" => $filename,
					"ext" => $ext);

	}

	private function storeURLImgFile($img_id){

		$filename = pathinfo($this->name, PATHINFO_BASENAME);
		$ext = pathinfo($this->name, PATHINFO_EXTENSION);

		$new_filename = $this->makeFileName($this->reff_id, $img_id, $ext);
		$target = $this->path_upload  . $this->reff_type."/" . $new_filename;
		$error = false;




        file_put_contents($target , file_get_contents($this->name));
		$f_type = mime_content_type($target);



	 	return array("url" => $target,
	 				"content_type" => $f_type,
					"filename" => $filename,
					"ext" => $ext);

	}

	public function store($reff_id = null){
		$this->checkFolder();
		$img = new ImgDAO();
		$status = false;


		$proto["type"] = $this->type;
		$proto["reff_id"] = $this->reff_id;
		$proto["reff_type"] = $this->reff_type;

		if($reff_id){
			$proto["reff_id"] = $reff_id;
		}


		if($img->save($proto)){
			$sumary = $img->getSumary();



			switch ($this->type) {
				case self::ALLOW_TYPE_RAW_IMG:
					$data = $this->storeRawImgFile( $sumary->new_id);
					break;
				case self::ALLOW_TYPE_URL_IMG:
					$data = $this->storeURLImgFile($sumary->new_id);
					break;
				default:
					$data = $this->storeImgFile( $sumary->new_id);
					break;
			}




			if(!is_null($data)){
				$this->last_insert_id = $sumary->new_id;

				$proto['img_id'] = $sumary->new_id;
				$proto = array_merge($proto, $data);

				if(!$img->save($proto)){
					$this->addDbErrors(array(), $img->errors);
				}else{
					$status=true;
				}
			}
		}

		return $status;
	}

	public function storeAction(){
		$img = new ImgDAO();

		$id = $this->getRequestAttr('id');

		$proto = $this->fillPrototype($img->getPrototype());



		//save
		if($img->save($proto)){
			$sumary = $img->getSumary();

			$data = $this->storeImgFile( $sumary->new_id);
			if(!is_null($data)){
				$proto['img_id'] = $sumary->new_id;
				$proto = array_merge($proto, $data);

				if($img->save($proto)){

					if(isset($this->callbackClass) && isset($this->callBackMethod) ){
						$my_class = $this->callbackClass;
						$method = $this->callBackMethod . $this->getActionSufix();

						//echo var_dump($my_class);
						$my_class->$method($this->reff_id, $sumary->new_id, $proto["url"]);

						unset($_SESSION["ImgHandler"][$this->name]);
					}

					$this->windowReload(Environment::$START_HANDLER);
				}else{
					$cols = $this->labelsDefinition();
					$this->addDbErrors($cols->getRelation(), $img->errors);
					$this->sendErrors();
				}


			}else{
				$this->addError(showMessage("file_error"));
				$this->sendErrors();
			}

		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $img->errors);
			$this->sendErrors();

		}
	}

	/**
	 *
	 * @return TableColumns
	 */
	public function &labelsDefinition(){
		$campos = new TableColumns();
		$campos->addColumn('img_id', showMessage('id'));
		$campos->addColumn('description', showMessage('description'));
		$campos->addColumn('url', showMessage('url'));

		return $campos;
	}

	public function inactivateAction(){
		$img = new ImgDAO();

		$id = $this->getRequestAttr('id');

		$proto = array();

		if($id){
			$proto['img_id'] = $id;
		}


		$proto['active']	=	SimpleDAO::REG_DESACTIVADO_TX;

		//save
		if(!$this->haveErrors() && $img->save($proto)){
			$this->asyncLoad(Handler::$handler, Environment::$APP_CONTENT_BODY, array(
				'do'=>'listWorkspace'
			));
		}else{
			$cols = $this->labelsDefinition();
			$this->addDbErrors($cols->getRelation(), $img->errors);
			$this->sendErrors();

		}
	}

	public function showAction($id=null, $bundle_path=''){

		if(!$id){
			$id = $this->getRequestAttr("id", false);
		}


		$dao = new ImgDAO();
		$dao->getById(array("img_id"=>$id));

		$img_info = $dao->get();

		if($img_info){
			$this->displayImg($img_info, $bundle_path);
		}
	}

	public function showByReff($reff_id, $reff_type, $bundle_path=""){
		$dao = new ImgDAO();
		$dao->getActives($reff_type, $reff_id);
		$img_info = $dao->get();
		//var_dump($img_info);exit();
		if($img_info){
			$this->displayImg($img_info, $bundle_path);
		}
	}

	private function displayImg($img_info, $bundle_path ){
		header('Content-Type: ' . $img_info["content_type"]);
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);

		if($this->is_attachment ){
			header('Content-disposition: attachment; filename="'.$img_info["filename"].'"');
		}

		header("Pragma: no-cache");
		$f = file_get_contents($bundle_path . $img_info["url"]);

		echo $f;
	}

	public function removeAction(){
		$id = $this->getRequestAttr('id');

		$dao = new ImgDAO();
		$dao->getById(array("img_id"=>$id));

		$img_info = $dao->get();

		if($dao->deleteByID(array("img_id" => $id))){
			unlink($img_info["url"]);

			Handler::asyncLoad("Img", "dash_assoc_img-" . $this->name, array(
				"do" => "list",
				"reff_id" => $this->reff_id,
				"reff_type" => $this->reff_type,
				"type" => $this->type,
				"name" => $this->name
			));
		}else{
			$this->addError(showMessage("delete_img_error"));
			$this->sendErrors();
		}



	}

	private function makeFileName($reff_id, $img_id, $ext){
		$name = "";

		switch ($this->file_name_mode) {
			case self::MODE_NAME_REFF_AND_ID:
				$name = $reff_id . "_" . $img_id . "." . $ext;
				break;

			case self::MODE_NAME_REFF_ID:
				$name = $reff_id . "." . $ext;
				break;

			default:
				$name = $reff_id . "_" . $img_id . "." . $ext;
				break;
		}

		return $name;
	}

	public function setModeFilename($mode){
		switch ($mode) {
			case self::MODE_NAME_REFF_AND_ID:
			case self::MODE_NAME_REFF_ID:
				$this->file_name_mode = $mode;
				break;

			default:
				$this->file_name_mode = self::MODE_NAME_REFF_AND_ID;
			break;
		}

	}

	public function removeOthers($id=null, $reff_id=null, $reff_type=null){
		if(!$id){
			$id= $this->getLastInsertId();
		}

		if(!$reff_id){
			$reff_id= $this->reff_id;
		}

		if(!$reff_type){
			$reff_type = $this->reff_type;
		}

		$dao = new ImgDAO();

		return $dao->deleteOthers($reff_type, $reff_id, $id);
	}

	public function getLastInsertId(){
		return $this->last_insert_id;
	}

	public function forceAttachment(){
		$this->is_attachment =true;
	}

	public function UseIdInName()
	{
		$this->idInName = true;
	}

	public function NotUseIdInName()
	{
		$this->idInName = false;
	}

	public static function getImagePath($id){
		$img= new ImgDAO();
		$img->getById(array("img_id"=>$id));
		$data = $img->get();

		return $data["url"];
	}
}


