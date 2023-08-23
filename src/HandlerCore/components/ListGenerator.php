<?php
namespace HandlerCore\components;
use HandlerCore\models\dao\AbstractBaseDAO;

use function HandlerCore\showMessage;

/**
 *
 */
class ListGenerator extends Handler {
	private $dao;
	private $showField;
	public $msgNoRecord;
	public $name="";
	public $cancelLink="";

	public $squema_list;

	function __construct( AbstractBaseDAO $dao) {
        $this->dao = $dao;
		$this->squema_list = 'views/common/list.php';
    }

	function setShowField($field){
		$this->showField=$field;
	}

	function show(){
		if(!$this->msgNoRecord){
			$this->msgNoRecord=showMessage("defaultNoRecord");
		}
		$this->display($this->squema_list, get_object_vars($this));
	}
}
