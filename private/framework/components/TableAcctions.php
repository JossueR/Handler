<?php
/**
*Create Date: 09/24/2012
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision:$
*/
class TableAcctions {
	private $allActions;
	
	const EDIT_ICON="icon-1";
	const SELECT_ICON="icon-5";
	
	function __construct() {
		$this->allActions = array();
	}
	
	public function addAction($text, $action, $html= null){
		if(!$html){
			$html = array();
		}
		
		$i = count($this->allActions);
		
		$this->allActions[$i]["TEXT"] = $text;
		$this->allActions[$i]["ACTION"] = $action;
		$this->allActions[$i]["HTML"] = $html;
	}
	
	public function getAllActions(){
		return $this->allActions;
	}

}
?>
