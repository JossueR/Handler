<h5><?php echo $title; ?></h5>
<ul class="greyarrow">
	<?php
		foreach ($items as $item) {
			?>
			<li><a href="javascript: void(0)" onclick="<?php echo $item[1]; ?>"><?php echo $item[0]; ?></a></li>
			<?php
		}
	?>
</ul>