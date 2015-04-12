
				
<div class="col-lg-12">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title"><i class="fa fa-list-alt fa-lg fa-border"></i></h3>
		</div>
		<div class="panel-body">
			<button class="btn btn-success" type="button" onclick="<?php echo Handler::asyncLoad("Rol", APP_CONTENT_BODY, array("do"=>"form"),true, true); ?>"><i class="fa fa-plus-circle fa-fw"></i><?php echo showMessage('nuevo'); ?></button>
			<?php
				$f = $this->getVar("f");
				$f->show();
			?>
		</div>
	</div>
</div>