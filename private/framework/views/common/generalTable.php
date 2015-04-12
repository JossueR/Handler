<?php
/**
*Create Date: 08/23/2012 10:56:11
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision:$
*/

$totals = array();
$sumary = $dao->getSumary();

?>
	<table id="tabla_<?php echo $name; ?>"
		<?php 
			$this->genAttribs($html);
		?>>
			<tbody>
				<tr>
					<?php
					//busca cada campo buscado en la lista datos
					$x=0;
					foreach ($fields as $field) 
					{
					?>
					<th class="table-header">
						<a class="campo-ordenable" href="javascript: void(0)" rel="<?php echo $field; ?>"><?php 
							echo $legent[$field]; 
						?></a>
					</th>
					<?php
						$x++;
					}
					
					if($actions){
						?>
						<th class="table-header"></th>
						<?php
					}
					
					if($totalVerticalClausure){
						?>
						<th class="table-header"></th>
						<?php
					}
					?>
				</tr>
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
							$htmlCol = $colClausure($row, $field);
							
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
						<ul class="icons_bar">
						<?php
							foreach ($actions as $act) {
								foreach ($row as $col_name => $col_val) 
								{
									$act['ACTION'] = str_replace("%23".$col_name."%23", $col_val, $act['ACTION']);
									$act['ACTION'] = str_replace("#".$col_name."#", $col_val, $act['ACTION']);
								}
								?>
								<li>
									<a href="javascript: void(0)" onclick="<?php echo $act['ACTION']; ?>" <?php $this->genAttribs($act['HTML']); ?> title="<?php echo $act['TEXT']; ?>">
										<?php echo $act['TEXT']; ?>
									</a>
								</li>
								<?php
							}
						?>
						</ul>
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
								if($colClausure){
									$htmlCol = $colClausure($totals, $field);
									
									if(array_key_exists("data", $htmlCol)){
										$colData = $htmlCol["data"];
										unset($htmlCol["data"]);
									}
								}else{
									$colData = (isset($totals[$field]))? $totals[$field] : "&nbsp;";
								}
							?>
							<td <?php if($colClausure){echo $this->genAttribs($htmlCol);}?> >
								<?php echo (isset($totals[$field]))? $colData : "&nbsp;"; ?>
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
		
		
	
<?php
	if($pagin){
		$params["do"] = $reloadDo;
		$params["objName"] = $name;
		
	
		$this->showPagination($name, $sumary->allRows, $reloadScript, $params, $controls);
	}
?>