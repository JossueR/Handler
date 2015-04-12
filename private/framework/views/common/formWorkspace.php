<?php
	$f = $this->getVar("f");
	$t = $this->getVar("title");
?>
<div class="col-lg-6">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title"><i class="fa fa-gears fa-lg fa-fw"></i><?php echo ($t)? $t : ""; ?></h3>
		</div>
		<div class="panel-body">
			<?php
				if($f){
					$f->show();
				}
			?>
		</div>
	</div>
</div>