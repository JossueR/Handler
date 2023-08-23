<?php
/**
*Create Date: 08/23/2012 10:56:11
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 117 $
 * este es el que se usa
*/

$totals = array();
$sumary = $dao->getSumary();

?>
	<table id="tabla_<?php echo $name; ?>"
		<?php 
			$this->genAttribs($html);
		?>>
			<?php
			if($show_labels){
			?>
			<thead>
				<tr>
					<?php
					//busca cada campo buscado en la lista datos
					$x=0;
					foreach ($fields as $field) 
					{
					?>
					<th class="header">
						<?php 
						if(in_array(TableGenerator::CONTROL_ORDER, $controls)){
						?>
						<a class="campo-ordenable" href="javascript: void(0)" rel="<?php echo $field; ?>"><?php 
						}
							echo ucwords( (isset($legent[$field]))? $legent[$field] : showMessage($field) ); 
						if(in_array(TableGenerator::CONTROL_ORDER, $controls)){
						?></a>
						<?php
						}
						?>
					</th>
					<?php
						$x++;
					}
					
					if($actions){
						?>
						<th class="header"></th>
						<?php
					}
					
					if($totalVerticalClausure){
						?>
						<th class="header"></th>
						<?php
					}
					?>
				</tr>
			</thead>
			<?php
			}
			?>
			<tbody>
				<?php
				//imprime los datos
				while ($row = $dao->get() ) 
				{
					//llama a funcion para calcular totales
					if(isset($totalsClausure)){
						$totals = $totalsClausure($totals, $row);
					}
					
					if($rowClausure){
						$htmlRow = $rowClausure($row);
					}else{
						$htmlRow = array();
					}
				?>
				<tr <?php $this->genAttribs($htmlRow); ?> >
					
					<?php
					$x=0;
					//imprime las columnas
					foreach ($fields as $field)
					{
						$colData = (isset($row[$field]))? $row[$field]: null;
						$htmlCol = array();
						
						if($colClausure){ 
							$htmlCol = $colClausure($row, $field, false);
							
							if(array_key_exists("data", $htmlCol)){
								$colData = $htmlCol["data"];
								unset($htmlCol["data"]);
							}
						}
					?>
					<td <?php $this->genAttribs($htmlCol); ?> >
						<?php echo $colData; ?>
					</td>
					<?php
					}
					
					//imprime la columna de totales verticales
					if( isset($totalVerticalClausure) ){
					?>
					<td>
						<?php
							echo $totalVerticalClausure($row);
						?>
					</td>
					<?php
					}

					//imprime la columna de actions
					if(( !isset($actionClausure) || $actionClausure($row) ) && $actions){
					?>
					<td>
						
						<?php
							foreach ($actions as $act) {
								
								if(!isset($act['HTML']["class"])){
									$act['HTML']["class"] = " text-primary";
								}else{
									$act['HTML']["class"] .= " text-primary";
								}
								
								$attrs = $this->genAttribs($act['HTML'],false);
								
								foreach ($row as $col_name => $col_val) 
								{
									$act['ACTION'] = str_replace("%23".$col_name."%23", $col_val, $act['ACTION']);
									$act['ACTION'] = str_replace("#".$col_name."#", $col_val, $act['ACTION']);
									
									
									$attrs = str_replace("%23".$col_name."%23", $col_val, $attrs);
									$attrs = str_replace("#".$col_name."#", $col_val, $attrs);
									
								}
								
								
								?>
								
										<a href="javascript: void(0)" onclick="<?php echo $act['ACTION']; ?>" <?php echo $attrs; ?> title="<?php echo $act['TEXT']; ?>" >
											<?php echo $act['TEXT']; ?>
										</a>
									
								<?php
							}
						?>
						
					</td>
					<?php
					}
					?>
					
					
				</tr>
				<?php
				}
				?>
				<?php
				//totales
				if(isset($totalsClausure)){
				?>
						<tr class="totales">
							<?php
							//imprime las columnas
							foreach ($fields as $field)
							{
								$colData = (isset($totals[$field]))? $totals[$field] : "&nbsp;";
								
								if($colClausure){
									$htmlCol = $colClausure($totals, $field, true);
									
									if(array_key_exists("data", $htmlCol)){
										$colData = $htmlCol["data"];
										unset($htmlCol["data"]);
									}
								}
							?>
							<td <?php if($colClausure){echo $this->genAttribs($htmlCol);}?> >
								<?php 
								if($colData instanceof ButtonMaker){
									$colData->show();
								}else{
									echo $colData;
								}
								?>
							</td>
							<?php
							}
							?>
						</tr>
				<?php
				}
				?>
			</tbody>
		</table>