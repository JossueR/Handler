			<form id="file-form" action="Img" method="post" enctype="multipart/form-data">
			  <?php
				
					foreach ($this->getAllVars() as $paramName => $value) {
						if($value != ""){
							?>
							<input  type="hidden" name="<?php echo $paramName; ?>" value="<?php echo $value?>" />
							<?php
						}
					}
				
				?>
			  <input type="hidden" name="do" value="store"/>
			  
			  <div class="form-group"><?php echo showMessage("image"); ?>:
			  	<input type="file" id="file-select" name="photo" />
			  </div>
			  <div class="form-group"><?php echo showMessage("description"); ?>:		
				<textarea name="description" rows="3" class="form-control"></textarea>
			  </div>
			  <button class="btn btn-success" type="submit"><?php echo showMessage("upload"); ?></button>
			  
			  
			  
			</form>
