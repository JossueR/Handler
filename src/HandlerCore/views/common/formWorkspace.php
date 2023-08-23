<?php
	$f = $this->getVar("f");
	$t = $this->getVar("title");
?>
<div class="row">
	<div class="col-lg-6">
		<div class="box card-primary">
			<div class="card-header">
				<h3 class="card-title"><i class="fa fas fa-cogs fa-lg fa-fw"></i><?php echo ($t)? $t : ""; ?></h3>
			</div>
			<div class="card-body table-responsive no-padding">
				<?php
					if($f){
						$f->show();
					}
				?>
			</div>
		</div>
	</div>
</div>