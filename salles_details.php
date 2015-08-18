<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET["id"];

// Détails du forfait
$querySalle = $db->prepare("SELECT * FROM salle WHERE salle_id=?");
$querySalle->bindParam(1, $data);
$querySalle->execute();
$salle = $querySalle->fetch(PDO::FETCH_ASSOC);
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Détails de la salle <?php echo $salle["salle_name"];?> | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
				<div class="btn-toolbar" id="top-page-buttons">
                   <a href="salles.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à la liste des salles</a>
                </div> <!-- btn-toolbar -->
               <h1 class="page-title"><span class="glyphicon glyphicon-credit-card"></span> Salle <?php echo $salle["salle_name"];?></h1>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>