<?php



?>
<div class="<?php echo $class; ?>" id='<?php echo $name; ?>'>
	<?php
	foreach ($data as $value) {
		switch ($value["type"]) {
			case WrapperViewer::TYPE_RAW :
				echo $value["action"];
			break;
			
			case WrapperViewer::TYPE_OBJ :
				$value["action"]->show();
			break;
			
			case WrapperViewer::TYPE_PATH :
				$this->display($value["action"]);
			break;
		}
	}
	?>
</div>