<?php
/**
*Create Date: 08/20/2011 11:08:01
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 166 $
 * Ya no se usa
*/

?>
<?php
	$_disabled = "";
	if($disabled){
		$_disabled = " disabled ";
	}

if($name)
{
	$_enctype = "";
	if($encType){
		$_enctype = 'enctype="multipart/form-data"';
	}
?>
<div >
<form role="form" name="<?php echo $name; ?>" id="<?php echo $name; ?>" method='POST' action='<?php echo $action; ?>' <?php echo $_enctype; ?>
    <?php
    if(!$encType){
    ?>
    onsubmit="send_form('<?php echo $name; ?>', '<?php echo $resultID;?>', '<?php echo $actionDO; ?>'); return false;"
    <?php
    }
    ?>
>
<?php
}
?>
	
			<?php
			//para cada campo del prototipo
			foreach ($prototype as $campo => $value) {
				if($sufix != ""){
					$nombreCampo = $prefix . $campo . "[]";
				}else{
					$nombreCampo = $prefix . $campo . $sufix;
				}
				
				//arma id del campo
				$idCampo = $prefix . $campo . $sufix;
				
				//obtiene los attibutos html
				$attrs = (isset($html[$campo]))? $this->genAttribs($html[$campo], false) : null;
				
				//obtiene clase de requerido
				$req_class = ($requireds[$campo])? "has-error" : ""; 
			?>
			<div class="form-group <?php echo $req_class; ?>"<?php 
			
				if(isset($wraper[$campo])){
					?> name="<?php echo $wraper[$campo]; ?>" id="<?php echo $wraper[$campo]; ?>"<?php
				}
			
			?>>
				<?php 
			
				
				if (!isset($types[$campo]) ||  ($types[$campo] != "div"  && $types[$campo] != "hidden")){
					echo (isset($legents[$campo]))? ucwords($legents[$campo]) : ucwords(showMessage($campo));
					echo ":";
				}
				  
				
				 ?>
			
					<?php 
					if(isset($types[$campo])){
						
						

						switch($types[$campo]){
							
							case "file":
								?>
								<input type="file" name="<?php echo $nombreCampo?>" <?php echo $_disabled; ?>  />
								<?php
							break;
							
							case "label":
								?>
								<span><?php echo $value; ?></span>
								<?php
							break;
							
							case "password":
								?>
								<input class="form-control"  type="password" name="<?php echo $nombreCampo?>" value="<?php echo $value?>"  <?php echo $attrs; ?> <?php echo $_disabled; ?>  />
								<?php
							break;
							
							
							
							case "hidden":
								?>
								<input class="form-control"  type="hidden" name="<?php echo $nombreCampo?>" value="<?php echo $value?>"  <?php echo $attrs; ?> <?php echo $_disabled; ?> />
								<?php
							break;
							
							case "textarea":
								?>
								<textarea class="form-control" rows="3" name="<?php echo $nombreCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> ><?php echo $value; ?></textarea>
								<?php
							break;
							
							case "radio":
								if(isset($sources[$campo]) && $sources[$campo] instanceof AbstractBaseDAO )
								{
									$dao = $sources[$campo];
								?>
								
									<?php
									while ($row = $dao->get()) {
										$selected = ($row[$dao->selectID] == $value)? "checked" : "";
										?>
										<label for="" class="">
											<?php echo $row[$dao->selectName]?>
											<input class="form-control"  type="radio" name="<?php echo $nombreCampo?>" value="<?php echo $row[$dao->selectID]?>" <?php echo $attrs; ?> <?php echo $_disabled; ?>  />
										</label>
										<?php
									}
									?>

								<?php
								}
							break;
							
							case "check":
								if(isset($sources[$campo]) && $sources[$campo] instanceof AbstractBaseDAO )
								{
									$dao = $sources[$campo];
								?>
								
									<?php
									while ($row = $dao->get()) {
										$selected = ($row[$dao->selectID] == $value)? "checked" : "";
										?>
										<label for="" class="">
											<?php echo $row[$dao->selectName]?>
											<input class="form-control"  type="checkbox" name="<?php echo $nombreCampo?>[]" value="<?php echo $row[$dao->selectID]?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> />
										</label>
										<?php
									}
									?>

								<?php
								}
							break;
							
							case "check-array":
								if(isset($sources[$campo]) && is_array($sources[$campo])  )
								{
									//divide los valores en un arreglo
									$value_array = explode(",", $value);
									?>
									<fieldset>
									<?php
									$f_i=0;
									foreach($sources[$campo] as $key_s => $val_s){
										//si la llave esta en el arreglo de valores seleccionados
										$selected = (in_array($key_s , $value_array))? "checked" : "";
										?>
										<div class="form-group">
											<div class="checkbox" >
												<label>
													<input type="checkbox" 
													name="<?php echo $nombreCampo?>[]" 
													id="<?php echo $idCampo . "." . $f_i; ?>" 
													<?php echo $attrs; ?> 
													<?php echo $_disabled; ?> value="<?php echo $key_s?>" 
													<?php echo $selected; ?> 
													/>
													<?php echo $val_s; ?>
												</label>
											</div>
										</div>
										<?php
										$f_i++;
									}
									?>
									<hr />
									</fieldset>
								<?php
								}
							break;
							
							case "select":
								if(isset($sources[$campo]) && $sources[$campo] instanceof AbstractBaseDAO )
								{
									$dao = $sources[$campo];
								?>
								<div class="form-group">
									<select class="form-control select2" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> >
									<option></option>
									<?php
									while ($row = $dao->get()) {
										$selected = ($row[$dao->selectID] == $value)? "selected" : "";
										?>
										<option value="<?php echo $row[$dao->selectID]?>" <?php echo $selected; ?>>
											<?php echo $row[$dao->selectName]?>
										</option>
										<?php
									}
									?>
									</select>
									<script>
										$("#<?php echo $idCampo?>").select2();
									</script>
								</div>
								<?php
								}
							break;
							
							case "select-i18n":
								if(isset($sources[$campo]) && $sources[$campo] instanceof AbstractBaseDAO )
								{
									$dao = $sources[$campo];
								?>
								<div class="form-group">
									<select class="form-control select2" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> >
									<option></option>
									<?php
									while ($row = $dao->get()) {
										$selected = ($row[$dao->selectID] == $value)? "selected" : "";
										?>
										<option value="<?php echo $row[$dao->selectID]?>" <?php echo $selected; ?>>
											<?php echo showMessage($row[$dao->selectName]); ?>
										</option>
										<?php
									}
									?>
									</select>
									<script>
										$("#<?php echo $idCampo?>").select2();
									</script>
								</div>
								<?php
								}
							break;
							
							case "select-array":
								if(isset($sources[$campo]) && is_array($sources[$campo])  )
								{
									
								?>
								<div class="form-group">
									<select class="form-control select2" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> >
										<option></option>
										<?php
										foreach($sources[$campo] as $key_s => $val_s){
											$selected = ($key_s == $value)? "selected" : "";
											?>
											<option value="<?php echo $key_s?>" <?php echo $selected; ?>>
												<?php echo $val_s; ?>
											</option>
											<?php
										}
										?>
									</select>
									<script>
										$("#<?php echo $idCampo?>").select2();
									</script>
								</div>
								<?php
								}
							break;
							
							case "div":
								?>
								<div name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> ><?php echo $value; ?></div>
								
								<?php
								
							break;
							
							case "search_select":
								
								?>
								<input <?php echo $_disabled; ?>  class="form-control" onchange="<?php echo $showAction[$campo]; ?>"  type="hidden" id="<?php echo "{$name}{$idCampo}"?>" name="<?php echo $nombreCampo; ?>" value="<?php echo $value?>" />
								<div name="<?php echo "{$name}-{$nombreCampo}"?>" id="<?php echo "{$name}-{$idCampo}"?>"  ></div>
								<button <?php echo $_disabled; ?>  class="btn btn-sm btn-warning" type="button" <?php echo $attrs; ?>>search</button>
								<?php
								
							break;
							
							case "multiple_select":
								
								?>
								
								
								<div id="multiple-<?php echo $idCampo?>"></div>
								
								<script>
									 new rvm_multiple('multiple-<?php echo $nombreCampo?>', '<?php echo $searchAction[$campo]; ?>', '<?php echo http_build_query($searchParams[$campo], '', '&'); ?>', '<?php echo showMessage('Add');?>');
								</script>
								<?php
								
							break;
							
							
							
							case "date":
								?>

								<div class="form-group">
				                  <div class="input-group">
				                    <div class="input-group-prepend">
				                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
				                    </div>
				                    
				                    <input <?php echo $_disabled; ?>  class="form-control"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="text"  value="<?php echo $value;?>" <?php echo $attrs; ?>>
				                  </div>
				                  <!-- /.input group -->
				                </div>
								
								<script>
									
									jQuery('#<?php echo $idCampo?>').datetimepicker({
									  timepicker:false,
									  format:'Y-m-d'
									});
								</script>
								<?php
							break;
							
							case "datetime":
								?>
								
								
								<div class="form-group">
				                  <div class="input-group">
				                    <div class="input-group-prepend">
				                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
				                    </div>
				                    
				                    <input <?php echo $_disabled; ?>  class="form-control"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="text" class="calendar_image" value="<?php echo $value;?>" <?php echo $attrs; ?>>
				                  </div>
				                  <!-- /.input group -->
				                </div>

								<script>
									
									$('#<?php echo $idCampo?>').datetimepicker({
									  format:'Y-m-d H:i'
									});
								</script>
								<?php
							break;

                            case "time":
                                ?>


                                <div class="form-group input-group">
                                    <input <?php echo $_disabled; ?>  class="form-control"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="text" class="calendar_image" value="<?php echo $value;?>" <?php echo $attrs; ?>>
									<span class="input-group-addon">

											<i class="fa fa-calendar" ></i>

									</span>
                                </div>

                                <script>

                                    $('#<?php echo $idCampo?>').datetimepicker({
                                        datepicker:false,
                                        format:'H:i'
                                    });
                                </script>
                                <?php
                            break;
							
							case "email":
								?>

								
								<div class="form-group input-group">
									<input <?php echo $_disabled; ?>  class="form-control"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="email" value="<?php echo $value;?>" <?php echo $attrs; ?>>
									<span class="input-group-addon">
										
											@
										
									</span>
								</div>
								
								<?php
							break;
							
							case "text":
							default:
								?>
								<input <?php echo $_disabled; ?>  class="form-control"  name="<?php echo $nombreCampo?>" type="text" class="inp-form-error" value="<?php echo $value;?>" <?php echo $attrs; ?>>
								<?php
						}
						
						
						
					}else{
						?>
						<input <?php echo $_disabled; ?>  class="form-control"  name="<?php echo $nombreCampo?>" type="text" value="<?php echo $value;?>" <?php echo $attrs; ?>>
						<?php
						}
					?>
				</div>
			<?php 
			}
			?>
	<?php
	if(is_array($params)){
		foreach ($params as $paramName => $value) {
			if($value != ""){
				?>
				<input class="form-control"  type="hidden" name="<?php echo $paramName; ?>" value="<?php echo $value?>" />
				<?php
			}
		}
	}
	?>
<?php
if($name)
{
	if(!$disabled)
	{
?>
	<br />
	<?php
	//si esta habilitado mostrar el boton ok
	if($enableButtonOK){
	?>
		<?php
		if(!$encType)
		{
		?>
			<input class="btn btn-success"  type="submit" value="ok"  />
		<?php
		}else{
			?>
			<input class="btn btn-success"  type="submit" value="ok"  />
			<?php
		}
		?>
	<?php
	}
	?>
	
	<?php
	//si esta habilitado mostrar el boton cancelar
	if($enableButtonCancel){
	?>
		<input class="btn btn-danger"  type="button" value="cancel" onclick="<?php echo $buttonCancelCommand; ?>" />
	<?php
	}
	?>	


	<?php
	}
	?>
</form>
</div>
<?php
}
?>