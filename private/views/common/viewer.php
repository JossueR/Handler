<div <?php $this->genAttribs($html); ?>>
	<?php
		if($title){
		?>
		<h3><?php echo $title; ?></h3>
		<?php	
		}
	?>
	<?php
		//imprime los datos
		if($row = $dao->get()){
	?>
	<table >
		<tbody>
		<?php
			foreach ($field_arr as $key) {
				?>
				<tr>
				<th><?php echo (isset($legent[$key]))? $legent[$key] : ucwords($key); ?>:</th>
				<td><?php echo $row[$key]; ?></td>
				</tr>
				<?php	
			}
		?>
		</tbody>
	</table>
	<?php
		}
	?>
</div>