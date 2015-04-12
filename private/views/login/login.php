<!doctype html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> 	<html lang="en"> <!--<![endif]-->
<head>

	<!-- General Metas -->
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">	<!-- Force Latest IE rendering engine -->
	<title>Login Form</title>
	<meta name="description" content="">
	<meta name="author" content="">
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /> 
	
	<!-- Stylesheets -->
	<link rel="stylesheet" href="<?php echo PATH_ROOT ?>css/login/base.css">
	<link rel="stylesheet" href="<?php echo PATH_ROOT ?>css/login/skeleton.css">
	<link rel="stylesheet" href="<?php echo PATH_ROOT ?>css/login/layout.css">
	<script src="<?php echo PATH_ROOT ?>js/prototype.js"></script>
	<script src="<?php echo PATH_ROOT ?>js/jquery-1.9.1.min.js"></script>
	<script src="<?php echo PATH_ROOT ?>js/funciones.js" type="text/javascript"></script>  
</head>
<body>

<?php 
	if(isset($error) )
	{
		?>
	<div class="notice">
		<p class="warn">Whoops! We didn't recognise your username or password. Please try again.</p>
	</div>
<?php
	}
?>


	<!-- Primary Page Layout -->

	<div class="container">
		
		<div class="form-bg">
			<form id="frmlogin" name="frmlogin" action="login">
				<h2>Login</h2>
				<p><input name="user" type="text" placeholder="Username"></p>
				<p><input name="pass" type="password" placeholder="Password"></p>
				<button  onclick="send_form('frmlogin', '<?php echo APP_HIDEN_CONTENT; ?>', 'login', ''); return false"></button>
			<form>
		</div>

	

	</div><!-- container -->
	<div id="comon_contend" style="display: none"></div>
<!-- End Document -->
</body>
</html>