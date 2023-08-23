<?php
	$f = $this->getVar("f");
	$t = $this->getVar("title");
	$name = $this->getVar("name");
	$full_size = $this->getVar("full_size");
	$mid_size = $this->getVar("mid_size");
	$class_size = $this->getVar("class_size");

	$link_new = $this->getVar("link_new");
	$buttons = $this->getVar("buttons");
	$content = $this->getVar("content");
	$script = $this->getVar("script");
	$script_params = $this->getVar("script_params");
	$type= $this->getVar("type");
    $panel_class= $this->getVar("panel_class");
	$icon_class= $this->getVar("icon_class");
	$body_class= $this->getVar("body_class");

	if(!isset($class_size)){
		$class_size = ($full_size)? 'col-lg-12' : 'col-lg-6';
		$class_size = ($mid_size)? 'col-lg-8' : $class_size;
	}

    if(!isset($panel_class)){
        $panel_class="card-primary";

    }

    if($icon_class == ""){
        $icon_class = "fa-link";
    }
?>
<div class="<?php echo $class_size; ?>">
	<div id="<?php echo $name; ?>_wraper" class="card card-outline <?php echo $panel_class; ?> ">
		<div class="card-header">
			<h3 class="card-title">
				<i class="fa <?php echo $icon_class; ?> fa-lg fa-fw"></i>
				<?php echo ($t)? $t : ""; ?>
			</h3>

			<div class="card-tools">
				<?php
					if($buttons){
                        if(!$buttons instanceof ButtonMaker){
                            $btnMaker = new ButtonMaker($name. "_btns",$invoker);
                            $btnMaker->addManyButtons($buttons);
                            $btnMaker->showInGroup();
                        }else{
                            $btnMaker = $buttons;
                        }

                        $btnMaker->show();
					}

					?>
				<button type="button" class="btn btn-tool" data-card-widget="maximize">
					<i class="fas fa-expand"></i>
				</button>
	        	<button type="button" class="btn btn-tool" data-card-widget="collapse">
	        		<i class="fas fa-minus"></i>
                </button>
	        </div><!-- /.box-tools -->
		</div>

		<?php
			if($f && $f instanceof TableGenerator){
				$body_class .= " p-0";
			}
		?>
		<div class="card-body <?php echo $body_class; ?>">


				<div id="<?php echo $name; ?>">
				<?php
					if($f){
						$f->show();
					}else if($content){
						echo $content;
					}else if($script){
						$this->display($script,$script_params);
					}
				?>
				</div>

		</div>
		<div class="card-footer">
			<?php
				if($f && $f instanceof FormMaker){
					//$f->formMakeButtons();
				}
			?>
		</div>
	</div>
</div>
<?php
	if(isset($postSripts)){
		foreach ($postSripts as $script) {
			echo $script;
		}
	}

?>
