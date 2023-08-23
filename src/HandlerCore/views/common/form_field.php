<?php
/**
*Create Date: 08/20/2011 11:08:01
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 166 $
*/


						switch($types[$campo]){
							
							case FormMaker::FIELD_TYPE_FILE:
								?>
								<input type="file" name="<?php echo $nombreCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> />
								<?php
							break;
							
							case FormMaker::FIELD_TYPE_LABEL:
								?>
								<span><?php echo $value; ?></span>
								<?php
							break;
							
							case FormMaker::FIELD_TYPE_PASSWORD:
								?>
								<input class="form-control <?php echo $req_class; ?>"  type="password" name="<?php echo $nombreCampo?>" value="<?php echo $value?>"  <?php echo $attrs; ?> <?php echo $_disabled; ?>  />
								<?php
							break;
							
							
							
							case FormMaker::FIELD_TYPE_HIDDEN:
								?>
								<input class="form-control"  type="hidden" name="<?php echo $nombreCampo?>" value="<?php echo $value?>"  <?php echo $attrs; ?> <?php echo $_disabled; ?> />
								<?php
							break;
							
							case FormMaker::FIELD_TYPE_TEXTAREA:
								?>
								<textarea class="form-control <?php echo $req_class; ?>" rows="3" name="<?php echo $nombreCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> ><?php echo $value; ?></textarea>
								<?php
							break;
							
							case FormMaker::FIELD_TYPE_RADIO:
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
							
							case FormMaker::FIELD_TYPE_CHECK:
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
							
							case FormMaker::FIELD_TYPE_CHECK_ARRAY:
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
													<input type="checkbox" class="<?php echo $req_class; ?>"
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
							
							case FormMaker::FIELD_TYPE_SELECT:
								if(isset($sources[$campo]) && $sources[$campo] instanceof AbstractBaseDAO )
								{
									$dao = $sources[$campo];
									//echo $dao->getSumary()->sql;
									if($req_class != ""){
										
									}
									
								?>
								<div class="form-group">
									<select class="form-control select2 <?php echo $req_class; ?>" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> >
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
							
							case FormMaker::FIELD_TYPE_SELECT_I18N:
								if(isset($sources[$campo]) && $sources[$campo] instanceof AbstractBaseDAO )
								{
									$dao = $sources[$campo];
								?>
								<div class="form-group">
									<select class="form-control select2 <?php echo $req_class; ?>" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> >
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
							
							case FormMaker::FIELD_TYPE_SELECT_ARRAY:
								if(isset($sources[$campo]) && is_array($sources[$campo])  )
								{
									
								?>
								<div class="form-group">
									<select class="form-control select2 <?php echo $req_class; ?>" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> <?php echo $_disabled; ?> >
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
							
							case FormMaker::FIELD_TYPE_DIV:
								?>
								<div name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> ><?php echo $value; ?></div>
								
								<?php
								
							break;
							
							case FormMaker::FIELD_TYPE_DATE:
								?>

								<div class="form-group">
				                  <div class="input-group">
				                    <div class="input-group-prepend">
				                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
				                    </div>
				                    
				                    <input <?php echo $_disabled; ?>  class="form-control <?php echo $req_class; ?>"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="text"  value="<?php echo $value;?>" <?php echo $attrs; ?>>
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
							
							case FormMaker::FIELD_TYPE_DATETIME:
								?>

								<div class="form-group">
				                  <div class="input-group">
				                    <div class="input-group-prepend">
				                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
				                    </div>
				                    
				                    <input <?php echo $_disabled; ?>  class="form-control <?php echo $req_class; ?>"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="text"  value="<?php echo $value;?>" <?php echo $attrs; ?>>
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

                            case FormMaker::FIELD_TYPE_TIME:
                                ?>

								<div class="form-group">
				                  <div class="input-group">
				                    <div class="input-group-prepend">
				                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
				                    </div>
				                    
				                    <input <?php echo $_disabled; ?>  class="form-control <?php echo $req_class; ?>"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="text" class="calendar_image" value="<?php echo $value;?>" <?php echo $attrs; ?>>
				                  </div>
				                  <!-- /.input group -->
				                </div>
                                

                                <script>

                                    $('#<?php echo $idCampo?>').datetimepicker({
                                        datepicker:false,
                                        format:'H:i'
                                    });
                                </script>
                                <?php
                            break;
							
							case FormMaker::FIELD_TYPE_EMAIL:
								?>

								<div class="input-group ">
				                  <div class="input-group-prepend">
				                    <span class="input-group-text">@</span>
				                  </div>
				                  <input <?php echo $_disabled; ?>  class="form-control <?php echo $req_class; ?>"  name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" type="email" value="<?php echo $value;?>" <?php echo $attrs; ?>>
				                </div>
								
								
								<?php
							break;
							
							case FormMaker::FIELD_TYPE_SEARCH_SELECT:
								
								if(isset($sources[$campo]) && $sources[$campo] instanceof AbstractBaseDAO )
								{
									$dao = $sources[$campo];
									$search_id_sql = $dao->getSumary()->sql;
									$search_id_sql .= " AND ".$dao->selectID."='".$value."'";
									$dao->find($search_id_sql);
									$row = $dao->get();
									
								}
								?>
				                <div class="input-group ">
				                  <div class="input-group-prepend">
				                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal_dest" <?php echo $attrs; ?> data-dest="<?php echo $idCampo; ?>">
										<i class="fas fa-search"></i>
									</button>
				                  </div>
				                  <!-- /btn-group -->
				                  <input type="hidden" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" value="<?php echo $value;?>" <?php echo $attrs; ?> />
				                  <input type="text" id="txt_<?php echo $idCampo?>" class="form-control <?php echo $req_class; ?>" disabled="disabled"  value="<?php echo $row[$dao->selectName]; ?>"  />
				                </div>
				                <?php
							break;
							
							case FormMaker::FIELD_TYPE_TEXT_SEARCH:
								?>
								<div class="input-group">
				                    <input type="text" name="<?php echo $nombreCampo?>" id="<?php echo $idCampo?>" <?php echo $attrs; ?> class="form-control">
				                    <span class="input-group-append">
				                      <button type="submit" class="btn btn-primary">
										<i class="fas fa-search"></i>
									</button>
				                    </span>
				                </div>
								<?php
							break;
							
							case FormMaker::FIELD_TYPE_TEXT:
							default:
								?>
								<input <?php echo $_disabled; ?>  class="form-control <?php echo $req_class; ?>"  name="<?php echo $nombreCampo?>" type="text" class="inp-form-error" value="<?php echo $value;?>" <?php echo $attrs; ?>>
								<?php
						}
					
?>