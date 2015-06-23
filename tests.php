<?php
require_once 'functions/db_connect.php';
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Template - Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-pencil"></span> Page Test !</h1>
 <button class='btn btn-default' id='test-1'><span class='glyphicon glyphicon-trash'></span></button>
                    <button class='btn btn-default' id='test-2'><span class='glyphicon glyphicon-trash'></span></button>
                     <button class='btn btn-default' id='test-3'><span class='glyphicon glyphicon-trash'></span></button>
                      <button class='btn btn-default' id='test-4'><span class='glyphicon glyphicon-trash'></span></button>
                       <button class='btn btn-default' id='test-5'><span class='glyphicon glyphicon-trash'></span></button>
                    <div id="add-options" class="popover popover-default">
                    	<div class="arrow"></div>
                    	<p style="font-weight:700;">Ajouter...</p>
                    	<button class="btn btn-default">Un cours</button>
                    	<button class="btn btn-default">Une réservation</button>
                    </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script>
	   $('button').click(function(){
		   var id;
		   id = '#'+$(this).attr('id');
		   console.log(id);
			$('#add-options').popoverX({
				target: id,
				placement: 'bottom',
				closeOtherPopovers: true,
			});
		   $('#add-options').popoverX('hide');
		   $('#add-options').popoverX('refreshPosition');		
		   $('#add-options').popoverX('show');
	   })
	</script>
</body>
</html>
<script>
</script>