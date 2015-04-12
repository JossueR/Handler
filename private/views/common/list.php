<ul class="list-group">
	<?php
		$i=0;
      	while ($e = $dao->get()) {
      		?>
      		<li class="list-group-item">
      			<?php
      			$link = $cancelLink;
      			foreach ($e as $col_name => $col_val) 
				{
					$link = str_replace("%23".$col_name."%23", $col_val, $link);
					$link = str_replace("#".$col_name."#", $col_val, $link);
				}
      			?>
      			<a href="javascript: void(0)" onclick="<?php echo $link;?>"><i class="fa fa-times-circle fa-lg fa-fw rojo"></i></a>
      			<?php 
					  echo (isset($e[$showField]))? $e[$showField] : "";
				 ?>
				 
      		</li>
      		<?php
      		$i++;
		}
	?>
</ul>
<?php
if($i==0){
	?>
<div class="well well-sm"><?php echo $msgNoRecord; ?></div>
	<?php
}