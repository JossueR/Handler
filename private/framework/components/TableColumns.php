<?php
/**
*Create Date: 09/24/2012
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision:$
*/
class TableColumns {
	private $legents;
	private $columns;
	
	function __construct() {
		$this->columns = array();
		$this->legents = array();
	}
	
	public function addColumn($column, $legent){
		$this->columns[] = $column;
		$this->legents[] = $legent;
	}
	
	public function getAllLegents(){
		if(count($this->legents) > 0){
			return $this->legents;
		}else{
			return null;
		}
		
	}
	
	public function getAllColumns(){
		if(count($this->columns) > 0){
			return implode(",", $this->columns);
		}else{
			return null;
		}
		
	}
	
	public function getRelation(){
		$rel = array();
		foreach ($this->columns as $index => $key) {
			$rel[$key] = $this->legents[$index];
		}
		
		return $rel;
	}
}
?>
