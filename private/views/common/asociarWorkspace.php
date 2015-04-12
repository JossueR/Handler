<?php
	$f = $this->getVar("f");
	$t = $this->getVar("title");
	$name = $this->getVar("name");
	$link_assoc = $this->getVar("link_assoc");
	$link_view = $this->getVar("link_view");
	$link_view = $this->getVar("link_view");
	$link_suggest = $this->getVar("link_suggest");
?>
<div class="col-lg-6">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title"><i class="fa fa-link fa-lg fa-fw"></i><?php echo ($t)? $t : ""; ?></h3>
		</div>
		<div class="panel-body">
			<p>
				<button class="btn btn-info" type="button" onclick="<?php echo $link_assoc;?>"><i class="fa fa-link fa-fw"></i><?php echo showMessage("assoc"); ?></button>
				<button class="btn btn-info" type="button" onclick="<?php echo $link_view;?>"><i class="fa fa-eye fa-fw"></i><?php echo showMessage("view"); ?></button>
				<?php
				if($link_suggest)
				{
				?>
				<button class="btn btn-info" type="button" onclick="<?php echo $link_suggest;?>"><i class="fa fa-lightbulb-o fa-fw"></i><?php echo showMessage("suggested"); ?></button>
				<?php
				}
				?>
			</p>
			<div id="<?php echo $name; ?>">
			<?php
				if($f){
					$f->show();
				}
			?>
			</div>
		</div>
	</div>
</div>