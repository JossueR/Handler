<?php
/**
*Create Date: 09/24/2012
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision:$
*/
class ChartGenerator {
	private $data;
	private $x_axis;
	private $y_axis;
	private $xSerieName;
	private $ySerieName;
	private $yName;
	
	function __construct($data) {
		$this->data = $data;
		$this->y_axis = array();
		$this->xSerieName = "";
		$this->ySerieName = "";
		$this->yName = array();
	}
	
	public function xSerieName($name){
		$this->xSerieName = $name;
	}
	
	public function ySerieName($name){
		$this->ySerieName = $name;
	}
	
	public function setXAxis($axis, $xSerieName=""){
		$this->x_axis = $axis;
		$this->xSerieName = $xSerieName;
	}
	
	public function setYAxis($axis, $yName=""){
		$this->y_axis[] = $axis;
		$this->yName[] = $yName;
	}
	
	
	public function generateJSON(){
		$serie = 1;
		$d = array();
		foreach ($this->y_axis as $y_axis) {
			$i=0;
			$point = array();
			$d['series']["d$serie"]["label"] = $this->yName[$serie-1];
			
			$d['series']["d$serie"]["bars"] = array("show" => true);
			
			foreach ($this->data as $row ) {
				$point = array();
				$point[] = $i;
				$point[] = floatval(str_replace(",", "", $row[$y_axis])) ;
				
				
				$d['series']["d$serie"]["data"][] = $point; 
				$i++;
			}
			
			$serie++;
		}
		
		$d['tick_x'] = $this->generateXLabel();
		$d['xSerieName'] = $this->xSerieName;
		$d['ySerieName'] = $this->ySerieName;
		$d['destStyle'] = array("class" => "flot-chart-content");
		
		$this->jsonHeaders();
		echo json_encode($d);
	}
	
	
	private function generateXLabel(){
		
		
		$i=0;
		$tick = array();
		
		foreach ($this->data as $row ) {
			$point = array();
			$point[] = $i;
			$point[] =  $row[$this->x_axis] ;
			
			$tick[] = $point;
			$i++;
		}

		return $tick;
		
	}
	
	private function jsonHeaders(){
		//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		//header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT"); 
		header("Cache-Control: no-cache, must-revalidate"); 
		header("Pragma: no-cache");
		header("Content-type: application/json");
	}

}
?>
