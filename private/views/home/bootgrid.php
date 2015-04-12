<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo APP_TITLE; ?></title>
    <link href="<?php echo PATH_ROOT ?>css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo PATH_ROOT ?>css/screen.css" rel="stylesheet">
    <link href="<?php echo PATH_ROOT ?>css/jquery.bootgrid.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo PATH_ROOT ?>font-awesome/css/font-awesome.min.css">
    
    <script src="<?php echo PATH_ROOT ?>js/jquery-1.9.1.min.js"></script>
    <script src="<?php echo PATH_ROOT ?>js/bootstrap.min.js"></script>
    <script src="<?php echo PATH_ROOT ?>js/jquery.bootgrid.min.js"></script>
    
    
    <style>
    	
    </style>
   </head>
    <body>
    	
    	
    	<table id="my-ajax-table" class="general">
		  <thead>
		    <tr>
		      <th class="table-header" data-column-id="category_id">category_id</th>
		      <th class="table-header" data-column-id="name">name</th>
		      <th class="table-header" data-column-id="active">active</th>
		    </tr>
		  </thead>
		  <tbody>
		    
		  </tbody>
		</table>
<script>
	$('#my-ajax-table').bootgrid({
  		ajax: true,
  		columnSelection: false,
  		rowCount: [15,25,50,-1],
  		url: "test?do=bootgrid",
  		
  		css:{
  			icon: "fa",
  			iconColumns: "fa-th-list",
  			iconDown: "fa-chevron-down",
  			iconRefresh: "fa-refresh",
  			iconUp: "fa-chevron-up"
  		},
  		templates:{
  	
  		search: "<div class=\"col-lg-4 \"><div class=\"form-group input-group\"><span class=\"input-group-addon\"><i class=\"fa fa-search\"></i></span><input type=\"text\" class=\"form-control {{css.searchField}}\" placeholder=\"{{lbl.search}}\"></div></div>"
  		},
  		labels: {
            infos: "Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries",
            loading: "<i class=\"fa fa-circle-o-notch fa-spin fa-2x \" ></i> Loading...",
            noResults: "No results found!",
            refresh: "Refresh",
            search: "Search"
        }
  		
	});
	
</script>

    </body>
   </html>