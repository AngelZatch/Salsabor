<?php
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accueil d'administration | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
			   <p id="current-time"></p>
			   <div class="jumbotron">
					<h1>Bonjour !</h1>
					<p>Bienvenue sur Salsabor Gestion. Que souhaitez-vous faire ?</p>
					<p>
						<a class="btn btn-primary btn-lg" href="inscription.php?status=contact"><span class="glyphicon glyphicon-user"></span> Réaliser une inscription</a>
						<a class="btn btn-primary btn-lg" href="vente_forfait.php"><span class="glyphicon glyphicon-credit-card"></span> Vendre un forfait</a>
						<a href="resa_add.php" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-record"></span> Réserver une salle</a>
						<a href="eleve_inviter.php" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-heart-empty"></span> Inviter un élève</a>
						<a href="passages.php" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-map-marker"></span> Consulter les passages</a>
						<a href="echeances.php" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-repeat"></span> Consulter les échéances</a>
						<a class="btn btn-primary btn-lg" href="read_rfid.php"><span class="glyphicon glyphicon-qrcode"></span> Lire un RFID (Admin)</a>
					</p>
			   </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>