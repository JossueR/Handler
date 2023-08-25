<?php
use HandlerCore\components\Handler;
use HandlerCore\Environment;
use function HandlerCore\showMessage;

?>
<div class="row"><div class="col-lg-12">
	<div class="box card-primary">
		<div class="card-header">
			<h3 class="card-title"><i class="fa fa-list-alt fa-lg fa-border"></i></h3>
		</div>
		<div class="card-body table-responsive no-padding">
			<button class="btn btn-success" type="button" onclick="<?php echo Handler::asyncLoad("Users", Environment::$APP_CONTENT_BODY, array("do"=>"form"),true, true); ?>"><i class="fa fa-plus-circle fa-fw"></i><?php echo showMessage('nuevo'); ?></button>
			<button class="btn btn-primary" type="button" onclick="<?php echo Handler::asyncLoad("Users", Environment::$APP_CONTENT_BODY, array("do"=>"listInactivesWorkspace"),true, true); ?>"><i class="fa fa-random fa-fw"></i><?php echo showMessage('inactivos'); ?></button>
			<?php
				$f = $this->getVar("f");
				$f->show();
				?></div>
		</div>
	</div>
</div>
