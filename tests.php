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
               <?php
    $calendar = $db->prepare('SELECT * FROM reservations JOIN salle ON (reservation_salle=salle.salle_id) JOIN prestations ON (type_prestation=prestations.prestations_id)');
    $calendar->execute();
    while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
		echo $row_calendar['reservation_start'];
		    }
?>

           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>
<script>
</script>