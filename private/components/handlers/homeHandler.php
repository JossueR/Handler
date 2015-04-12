<?php
    include( PATH_PRIVATE . "kernel.php");

	loadClass("models/dao/RecordsDAO.php");
	//loadClass("components/handlers/EndpointHandler.php");
	//loadClass("components/handlers/GroupEndpointHandler.php");
/**
 * 
 */
class homeHandler extends Handler {
	
	function inicioAction(){
		echo "OOOOOKKKKKK";
	}
	
	function indexAction(){
		$this->display("views/home/dashboard.php");
	}
	
	function makeAlertsAction(){
		
	}
	
	
	function stepsAction(){
		$total = count($_SESSION["HISTORY"]);
		
		$i=1;
		
		echo '<ol class="breadcrumb">';
		foreach ($_SESSION["HISTORY"] as $his) {
			
			$action = $his["ACTION"] . "?" . $his["GET"];
			$post = $his["POST"];
			echo "<li>";
			echo "<a href=\"javascript: void(0)\" onclick=\"dom_update('$action','$post','".APP_CONTENT_BODY."')\">".$his["TEXT"]."</a>";
			echo "</li>";
			$i++;
		}
		echo "</ol>";
	}
	
	function makeMenu($top , $actual="", $sub = false){
		
		if(!$top){
			$menu = array();
			$menu["dashbord"] = "#";
			$menu["enfermedades"] = "#";
			$menu["users"] = "#";
			$menu["free_module 1"] = "#";
			$menu["free_module 2"] = "#";
			$menu["free_module 3"] = array(
										"sub_module 1" => "#",
										"sub_module 2" => "#"
									);
									
			$top = $menu;
		}
		
		if(!$sub){
			echo '<ul class="nav navbar-nav side-nav">';
		}else{
			echo '<ul class="dropdown-menu">';
		}
		
		foreach ($top as $key => $value) {
			if(is_array($value)){
					
				echo '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-caret-square-o-down"></i>' . showMessage($key) . '<b class="caret"></b></a>';
				
				$this->makeMenu($value, $actual, true);
				
				echo '</li>';
			}else{
				
				echo '<li><a href="#"><i class="fa fa-table"></i>' . showMessage($key) . '</a></li>';
				
			}
		}
		
		echo '</ul>';
	}
	
	function dashboardAction(){
		$this->clearSteps();
		$this->registerAction("dashbord", "<i class=\"fa fa-fw fa-dashboard\"></i>". showMessage("dashbord"));
		$this->showTitle(showMessage("dashbord"));
		
		$dao = new RecordsDAO();
		$dao->autoconfigurable= true;
		TableGenerator::defaultOrder('date', false);
		
		$dao->getActives();
		$this->setVar("record", $dao);
		
		$filter = $this->filterFormAction(false);
		$this->setVar("filter", $filter);

		$this->display("views/home/dashlet_history.php");
		Handler::asyncLoad("home", APP_STEPS_BAR, array("do"=>"steps"));
	}
	
	public function filterFormAction($show = true)
	{
		$form = new FormMaker();
		$form->name = "filterFrm";
		$form->action = "home";
		$form->actionDO = "addFilter";
		
		 
		if(!isset($_SESSION["dashFilter"]["fechaDesde"])) $_SESSION["dashFilter"]["fechaDesde"] = "";
		if(!isset($_SESSION["dashFilter"]["fechaHasta"])) $_SESSION["dashFilter"]["fechaHasta"] = "";
		
		$form->prototype = array(
			"fechaDesde" => $_SESSION["dashFilter"]["fechaDesde"],
			"fechaHasta" => $_SESSION["dashFilter"]["fechaHasta"]
		);
		
								
		$form->defineField(array(
			"campo"=>'fechaDesde',
			"tipo" =>"date"
		));
		
		$form->defineField(array(
			"campo"=>'fechaHasta',
			"tipo" =>'date'
		));
		
		if($show){
			$form->show();
		}
		
		return $form;
	}
	
	
	function addFilterAction(){
		$proto = $this->fillPrototype(array(
			"fechaDesde" => "",
			"fechaHasta" => ""
		));
		
		$_SESSION["dashFilter"]["fechaDesde"] = $proto["fechaDesde"];
		$_SESSION["dashFilter"]["fechaHasta"] = $proto["fechaHasta"];
		
		Handler::asyncLoad("home", APP_CONTENT_BODY, array("do" => "dashboard"));
	}
}

?>
