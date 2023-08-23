<div class="col-lg-3">
    <div class="box <?php echo $class; ?>">
        <div class="card-header">
            <div class="row">
                <div class="col-xs-6">
                    <i class="fa <?php echo $icon; ?> fa-5x"></i>
                </div>
                <div class="col-xs-6 text-right">
                    <p class="announcement-heading" id='<?php echo $name; ?>' ><?php echo $cant; ?></p>
                    <p class="announcement-text"><?php echo $subtitle; ?></p>
                </div>
            </div>
        </div>
        <a href="#" onclick="<?php echo $link; ?>">
            <div class="card-footer announcement-bottom">
                <div class="row">
                    <div class="col-xs-6"> <?php echo $title; ?> </div>
                    <div class="col-xs-6 text-right">
                        <i class="fa fa-arrow-circle-right"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>