<?php

	if(isset($buttons)){
		if($in_group){
		?>
		<div class="btn-group">
		<?php
		}
		
		$sec = new DynamicSecurityAccess();
		
		foreach ($buttons as $key => $btn) {
			$type = (isset($btn["type"]))? $btn["type"] : "btn-info";
			$link = (isset($btn["link"]))? $btn["link"] : "";
			$icon = (isset($btn["icon"]))? $btn["icon"] : "";
			$attrs = (isset($btn["html"]))? $this->genAttribs($btn["html"],false) : "";
			
			if(count($params_data) > 0){
				foreach ($params_data as $col_name => $col_val) 
				{
					$link = str_replace("%23".$col_name."%23", $col_val, $link);
					$link = str_replace("#".$col_name."#", $col_val, $link);
				}
			}
			
			if($sec->checkDashButton($invoker, $name, $key)){
	?>
		<button class="btn <?php echo $type;?>" type="button" onclick="<?php echo $link;?>" <?php echo $attrs;?>>
			<i class="fa <?php echo $icon;?> "></i>
			<?php 
				if($show_label){
					echo showMessage($key); 
				}
			
			?>
		</button>
	<?php	
			}
		}
		
		if($in_group){
		?>
		</div>
		<?php
		}
	}
