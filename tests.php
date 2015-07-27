<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
include 'functions/reservations.php';
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
               <?php
	$length = 8;
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$chars_length = strlen($characters);
	$reference = '';
	for ($i = 0; $i < $length; $i++) {
		$reference .= $characters[rand(0, $chars_length - 1)];
	}
	echo $reference;
?>
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
		   $('#add-options').popoverX('toggle');
		   $('#add-options').on('show.bs.modal', function(){
			   $('#add-options').popoverX('refreshPosition');
		   });
	   })
	</script>
</body>
</html>
<script>
</script>