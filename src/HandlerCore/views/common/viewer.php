<?php
if($this->display_box){
?>
<div <?php $this->genAttribs($html); ?>>
	<div class="card <?php echo $this->panel_class; ?>">
		<div class="card-header">
			<?php
				if($title){
				?>
				<h3 class="card-title"><?php echo $title; ?></h3>
				<?php
				}
			?>
			<div class="card-tools">
                <?php
                if($buttons){
                    $buttons->show();
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
		<div class="card-body p-0">
<?php
}
?>
			<?php
				//imprime los datos
				if($row = $row_data){
			?>
			<table class="table table-condensed table-responsive">
				<tbody>
				<?php
					foreach ($field_arr as $key) {
						?>
						<tr>
						<th><?php echo (isset($legent[$key]))? $legent[$key] : ucwords(showMessage($key)); ?>:</th>
						<td><?php
							if($callbackShow){
								echo $callbackShow($key, $row[$key], $row);
							}else{
								echo $row[$key];
							}


						?></td>
						</tr>
						<?php
					}
				?>
				</tbody>
			</table>
			<?php
				}
			?>
<?php
if($this->display_box){
?>
		</div>
	</div>
</div>
<?php
}
?>
