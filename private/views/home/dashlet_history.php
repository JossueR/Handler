<div class="col-lg-12">
    <div class="panel panel-info">
      <div class="panel-heading">
      	<a data-toggle="collapse" data-parent="#accordion" href="#dash_filter">
        	<h3 class="panel-title"><i class="fa fa-filter"></i> <?php echo showMessage("filters"); ?></h3>
        </a>
      </div>
      <div class="panel-body panel-collapse collapse" id="dash_filter">
      	<div class="col-lg-4">
      	<?php
      	$form = $this->getVar("filter");
		$form->show();
      	?>
      	</div>
      </div>
    </div>
</div>

<?php echo time(); ?>
<div class="col-lg-4">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-clock-o"></i> <?php echo showMessage("recentActivities"); ?></h3>
      </div>
      <div class="panel-body">
      	<div class="list-group">
      	<?php
      	$dao = $this->getVar("record");
		$ix=0;
		$displayAll= false;
      	while ($e = $dao->get()) {
      		
      	?>
        
          <a class="list-group-item" href="#">
            <span class="badge"><?php 
            $e["secs"] = intval($e["secs"]);
			$e["mins"] = intval($e["mins"]);
			$e["horas"] = intval($e["horas"]);
			$e["dias"] = intval($e["dias"]);
			
            if($e["secs"] < 60){
            	
            	echo showMessage("secsAgo",array("unds" => $e["secs"] ));
				
            }else if($e["mins"] < 60){
            	
				echo showMessage("minsAgo",array("unds" => $e["mins"] ));
				
            }else if($e["horas"] < 24){
            	
				echo showMessage("hoursAgo",array("unds" => $e["horas"] ));
				
            }else{
				echo showMessage("daysAgo",array("unds" => $e["dias"] ));
            }
            
            $msg = ($e["desc"] == "")? showMessage($e["Action"]): showMessage($e["desc"]);
            ?></span>
            <i class="fa fa-check"></i> <?php echo $e["Action"] . ": " . $msg; ?>
          </a>
        
        <?php
        	$ix++;
			if($ix > 10){
				$displayAll = false ;
				break;
			}
		}
		?>
		</div>
		<?php
		if($displayAll){
			?>
			<div class="text-right">
	          <a href="#"><?php echo showMessage("viewAll");?><i class="fa fa-arrow-circle-right"></i></a>
	        </div>
			<?php
		}
        ?>
      </div>
    </div>
  </div>
  


<div class="col-lg-4">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> <?php echo showMessage("abiertas"); ?></h3>
      </div>
      <div class="panel-body">
      	<div class="flot-chart" id="chart_0">
      	
      	</div>
      </div>
    </div>
</div>

<div class="col-lg-4">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> <?php echo showMessage("cerradas"); ?></h3>
      </div>
      <div class="panel-body">
      	<div class="flot-chart" id="chart_1">
      	
      	</div>
      </div>
    </div>
</div>

<div class="col-lg-4">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> <?php echo showMessage("byService"); ?></h3>
      </div>
      <div class="panel-body">
      	<div class="flot-chart" id="chart_2">
      	
      	</div>
      </div>
    </div>
</div>

<div class="col-lg-4">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> <?php echo showMessage("byAfectados"); ?></h3>
      </div>
      <div class="panel-body">
      	<div class="flot-chart" id="chart_3">
      	
      	</div>
      </div>
    </div>
</div>

<div class="col-lg-4">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> <?php echo showMessage("byTipo"); ?></h3>
      </div>
      <div class="panel-body">
      	<div class="flot-chart" id="chart_4">
      	
      	</div>
      </div>
    </div>
</div>
<script>
	chart_0 = new ChartGenerator("Evento","do=chartOpenedBy","chart_0");
	chart_1 = new ChartGenerator("Evento","do=chartClosedBy","chart_1");
	chart_2 = new ChartGenerator("Evento","do=chartByServicio","chart_2");
	chart_3 = new ChartGenerator("Evento","do=chartByAfectado","chart_3");
	chart_4 = new ChartGenerator("Evento","do=chartByTipo","chart_4");
</script>