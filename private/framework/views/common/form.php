<?php
/**
*Create Date: 08/20/2011 11:08:01
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision:$
*/

?>
<?php
if($name)
{
?>
<div >
<form role="form" name="<?php echo $name; ?>" id="<?php echo $name; ?>" method='POST' action='<?php echo $action; ?>'>
<?php
}
?>
	
			<?php
			
			foreach ($prototype as $campo => $value) {
				if($sufix != ""){
					$nombreCampo = $prefix . $campo . "[]";
				}else{
					$nombreCampo = $prefix . $campo . $sufix;
				}
				
				$idCampo = $prefix . $campo . $sufix;
				$attrs = (isset($html[$campo]))? $this->genAttribs($html[$campo], false) : null;
			?>
			<div class="form-group"><?php 
				
				if (!isset($types[$campo]) ||  $types[$campo] != "div"){
					echo (isset($legents[$campo]))? ucwords($legents[$campo]) : ucwords($campo);
					echo ":";
				}
				  
				
				 ?>
			
					<?php 
					if(isset($types[$campo])){
						

						switch($types[$campo]){
							
							case "label":
								?>
								<span><?php echo $value; ?></span>
								<?php
							break;
							
							case "password":
								?>
								<input class="form-control"  type="password" name="<?php echo $nombreCampo?>" value="<?php echo $value?>"  <?php echo $attrs; ?>/>
								<?php
							break;
							
							
							
							case "hidden":
								?>
								<input class="form-control"  type="hidden" name="<?php echo $nombreCampo?>" value="<?php echo $value?>"  <?php echo $attrs; ?>/>
								<?php
							break;
							
							case "textarea":
								?>
								<textarea class="form-control" rows="3" name="<?php echo $nombreCampo?>" <?php echo $attrs; ?>><?php echo $value; ?></textarea>
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
											<input class="form-control"  type="radio" name="<?php echo $nombreCampo?>" value="<?php echo $row[$dao->selectID]?>" <?php echo $attrs; ?> />
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
											<input class="form-control"  type="checkbox" name="<?php echo $nombreCampo?>[]" value="<?php echo $row[$dao->selectID]?>" <?php echo $attrs; ?> />
										</label>
										<?php
									}
									?>

								<?php
								}
							break;
							
							case "select":
								if(isset($sources[$campo]) && $sources[$campo] instanceof AbstractBaseDAO )
								{
									$dao = $sources[$campo];
								?>
								<div class="form-group">
									<select class="form-control" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?>>
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
									<select class="form-control" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?>>
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
								</div>
								<?php
								}
							break;
							
							case "select-array":
								if(isset($sources[$campo]) && is_array($sources[$campo])  )
								{
									
								?>
								<div class="form-group">
									<select class="form-control" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?>>
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
								</div>
								<?php
								}
							break;
							
							case "div":
								?>
								<div name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> ></div>
								<?php
								
							break;
							
							case "search_select":
								
								?>
								<input class="form-control"  id="<?php echo $idCampo?>" name="<?php echo $nombreCampo?>" type="hidden" value="<?php echo $value;?>" <?php echo $attrs; ?>>
								<a href="javascript:void(0)" onclick="rvm_finder.open('dialog-<?php echo $idCampo?>')">search</a>
								<div id="result-<?php echo $idCampo?>"></div>
								<div id="dialog-<?php echo $idCampo?>" style="display: none"></div>
								<script>
									 
									 <?php 
									 	
								 		$p = $searchParams[$campo];
										$p["dialog"]= 'dialog-' . $idCampo;
										$p["showDest"]= 'result-' . $idCampo;
										
								 		$rf = $showParams[$campo]["returnField"];
										unset($showParams[$campo]["returnField"]);
										
									 ?>
									 rvm_finder.add('dialog-<?php echo $idCampo?>', '', '<?php echo $idCampo?>', '<?php echo $searchAction[$campo]; ?>','<?php echo http_build_query($p, '', '&'); ?>', '<?php echo $showAction[$campo]; ?>','<?php echo json_encode($showParams[$campo]); ?>', '<?php echo $rf; ?>',"<?php echo $value;?>");
								</script>
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

								
								<div class="form-group input-group">
									<input class="form-control"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="text" class="calendar_image" value="<?php echo $value;?>" <?php echo $attrs; ?>>
									<span class="input-group-addon">
										
											<i class="fa fa-calendar" ></i>
										
									</span>
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

								
								<div class="form-group input-group">
									<input class="form-control"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="text" class="calendar_image" value="<?php echo $value;?>" <?php echo $attrs; ?>>
									<span class="input-group-addon">
										
											<i class="fa fa-calendar" ></i>
										
									</span>
								</div>

								<script>
									
									jQuery('#<?php echo $idCampo?>').datetimepicker({
									  format:'Y-m-d H:i'
									});
								</script>
								<?php
							break;
							
							case "email":
								?>

								
								<div class="form-group input-group">
									<input class="form-control"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="email" value="<?php echo $value;?>" <?php echo $attrs; ?>>
									<span class="input-group-addon">
										
											@
										
									</span>
								</div>
								
								<?php
							break;
							
							case "text":
							default:
								?>
								<input class="form-control"  name="<?php echo $nombreCampo?>" type="text" class="inp-form-error" value="<?php echo $value;?>" <?php echo $attrs; ?>>
								<?php
						}
							
						
					}else{
						?>
						<input class="form-control"  name="<?php echo $nombreCampo?>" type="text" value="<?php echo $value;?>" <?php echo $attrs; ?>>
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
?>
</form>

<br />
	<table >
		<tr>
			<td>
				<input class="btn btn-success"  type="button" value="ok" onclick="send_form('<?php echo $name; ?>', '<?php echo $resultID;?>', '<?php echo $actionDO; ?>')" />
			</td>
			<td>
				<input class="btn btn-danger"  type="button" value="cancel" onclick="<?php echo $this->historyBack(); ?>" />
			</td>
		</tr>
	</table>
</div>
<?php
}
?>