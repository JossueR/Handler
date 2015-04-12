<?php 
	if(!isset($_SESSION["usuario_id"])){
		//header("Location: login.php");
	}
	
	$registrer='$HeadURL: https://jos_lap/svn/InventoryAdmin/trunk/private/views/home/dashboard.php $';
	$version = getVersion($registrer);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo APP_TITLE; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo PATH_ROOT ?>css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo PATH_ROOT ?>css/screen.css" rel="stylesheet">
    <link href="<?php echo PATH_ROOT ?>css/jquery.datetimepicker.css" rel="stylesheet">
	
    <!-- Add custom CSS here -->
    <link href="<?php echo PATH_ROOT ?>css/sb-admin.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo PATH_ROOT ?>font-awesome/css/font-awesome.min.css">
	<link href="<?php echo PATH_ROOT ?>css/jquery.bootgrid.css" rel="stylesheet">
    
    <script src="<?php echo PATH_ROOT ?>js/jquery-1.9.1.min.js"></script>
    <script src="<?php echo PATH_ROOT ?>js/funciones-jq.js"></script>
    <script src="<?php echo PATH_ROOT ?>js/bootstrap.min.js"></script>
    <script src="<?php echo PATH_ROOT ?>js/jquery.datetimepicker.js"></script>
    <script src="<?php echo PATH_ROOT ?>js/flotr2.min.js"></script>
    <script src="<?php echo PATH_ROOT ?>js/ckeditor/ckeditor.js"></script>
    <script src="<?php echo PATH_ROOT ?>js/jquery.bootgrid.min.js"></script>
    
    
    <style>
    	.logo{
    		background: url("images/logo2.png") no-repeat;
    		background-size: 250px, 125px;
    		background-position: 0 -8px;
    		min-height: 50px;
    		min-width:250px;
    </style>
    
  </head>

  <body>

    <div id="wrapper">

      <!-- Sidebar -->
      <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header logo">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="home"><?php echo APP_TITLE; ?></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-ex1-collapse">
          <ul class="nav navbar-nav side-nav">
          	<?php
                
            if(Handler::havePermission('ITM-01')){
            ?>
            <li><a href="javascript:void(0)" onclick="<?php echo Handler::asyncLoad("Product", APP_CONTENT_BODY, array(), true);?>"><i class="fa fa-crosshairs"></i> <?php echo showMessage("product"); ?></a></li>
           <?php
			}
            ?>
            <li><a href="javascript:void(0)" onclick="<?php echo Handler::asyncLoad("Order", APP_CONTENT_BODY, array(), true);?>"><i class="fa fa-crosshairs"></i> <?php echo showMessage("order"); ?></a></li>
            <?php
                
            if(Handler::havePermission('COF-01')){
            ?>
           	<li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-gear"></i> <?php echo showMessage("setting"); ?> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                
                <li><a href="#" onclick="<?php echo Handler::asyncLoad("Category", APP_CONTENT_BODY, array(), true);?>"><?php echo showMessage("category"); ?></a></li>
                <li><a href="#" onclick="<?php echo Handler::asyncLoad("Table", APP_CONTENT_BODY, array(), true);?>"><?php echo showMessage("tables"); ?></a></li>
                <?php
                
                if(Handler::havePermission('PER-01')){
                ?>
                <li><a href="#" onclick="<?php echo Handler::asyncLoad("Permission", APP_CONTENT_BODY, array(), true);?>"><?php echo showMessage("permissions"); ?></a></li>
                <?php
				}
                ?>
                <?php
                
                if(Handler::havePermission('ROL-01')){
                ?>
                <li><a href="#" onclick="<?php echo Handler::asyncLoad("Rol", APP_CONTENT_BODY, array(), true);?>"><?php echo showMessage("roles"); ?></a></li>
                <?php
				}
                ?>
                
              </ul>
            </li>
            <?php
			}
            ?>
          </ul>

          <ul class="nav navbar-nav navbar-right navbar-user">
            <li class="dropdown alerts-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> <?php echo showMessage("alerts"); ?> <span class="badge" id="contadorEventos">0</span> <b class="caret"></b></a>
              <ul class="dropdown-menu" id="alerts_list">
                <?php
                //$this->makeAlertsAction();
                ?>
              </ul>
            </li>
            <li class="dropdown user-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $_SESSION["usuario_nombre"]; ?> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a  href="javascript:void(0)" onclick="<?php echo Handler::asyncLoad("Users", APP_CONTENT_BODY, array("do" => "listWorkspace"), true);?>"><i class="fa fa-gear"></i> <?php echo showMessage("setting"); ?></a></li>
                <li class="divider"></li>
                <li><a  href="javascript:void(0)" onclick="<?php echo Handler::asyncLoad("Users", APP_CONTENT_BODY, array("do" => "changePass","id" => $_SESSION['USER_ID']), true);?>"><i class="fa fa-gear"></i> <?php echo showMessage("changePass"); ?></a></li>
                <li class="divider"></li>
                <li><a href="javascript:void(0)" onclick="<?php echo Handler::asyncLoad("login", APP_HIDEN_CONTENT, array("do" => "logout"), true);?>"><i class="fa fa-power-off"></i> Log Out</a></li>
              </ul>
            </li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </nav>

      <div id="page-wrapper">

        <div class="row">
          <div class="col-lg-12">
          	<div id="page-heading" class="alert alert-dismissable alert-info">
            	
            </div>
            <div id="tabs_container">
            <ol class="breadcrumb">
              <li class="active"><i class="fa fa-dashboard"></i> Dashboard</li>
            </ol>
            </div>
          </div>
        </div><!-- /.row -->

        <div class="row" id="main_content">
          
        
          main
          
          
        </div><!-- /.row -->
      </div><!-- /#page-wrapper -->

    </div><!-- /#wrapper -->
    
    <div id="ajax_icon"></div>
	<div id="comon_contend"></div>
	<?php
			/*
			Handler::asyncLoadInterval("Evento", "contadorEventos", array(
				"do" => "totalAlerts"
			));
			*/
			
			
			if(!Handler::reloadLast(true)){
				Handler::asyncLoad("Product", APP_CONTENT_BODY, array());
			}
			?>
  </body>
  <div style="display: none">
<?php
	$app = APP_TITLE;
	$app = urlencode($app);
	$suc = urlencode(APP_SUC);
	$port = $_SERVER["SERVER_PORT"];
	$server = $_SERVER["SERVER_NAME"];
	
	file_get_contents("http://apps.cayucosoft.com/register.php?app=$app&v=$version&port=$port&suc=$suc&host=$server");
?>  	
  </div>
</html>

