<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
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
               <h1 class="page-title"><span class="glyphicon glyphicon-map-marker"></span> Passages</h1>
               <p id="current-time"></p>
               <p id="last-edit">Aucun cours à venir pour le moment.</p>
               <?php while($passages = $queryPassages->fetch(PDO::FETCH_ASSOC)) { ?>
               <p><?php echo $passages["eleve_prenom"]." ".$passages["eleve_nom"]." enregistré à ".$passages["passage_date"];?></p>
			   <?php } ?>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script>
	   var now;
	   function update(){
		   var now = moment().locale('fr').format("DD MMMM YYYY HH:mm:ss");
		   $("#current-time").html(now);
	   }
	   
	   $(document).ready(function(){
		   update();
		   setInterval(update, 1000);
	   });
	</script>
</body>
</html>