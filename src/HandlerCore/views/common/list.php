<ul class="nav nav-stacked">
	<?php
		$i=0;
      	while ($e = $dao->get()) {
            $data_link = $link;
            foreach ($e as $col_name => $col_val)
            {
                $data_link = str_replace("%23".$col_name."%23", $col_val, $data_link);
                $data_link = str_replace("#".$col_name."#", $col_val, $data_link);
            }

      		?>

			<li>
      			<a  href="javascript: void(0)" onclick="<?php echo $data_link; ?>" >
                    <?php
                    if(!$colClausure){
                        echo $e["$main_text"];
                    }else{
                        echo $colClausure($e);
                    }

                    if(isset($subText))
                    {
                    ?>
                    <span class="pull-right badge bg-blue"><?php echo $e["$subText"]; ?></span>
                    <?php
                    }
                    ?>
                </a>
			</li>
      		<?php
      		$i++;
		}
	?>
</ul>
