<?php
include "functions/db_connect.php";
$db = PDOFactory::getConnection();

$date = date_create('now')->format('H:i:s');
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
						<h1><?php if($date > "06:00:00" && $date <= "12:00:00"){ ?>
							Vous êtes là tôt aujourd'hui... Bonjour !
							<?php } else if($date > "12:00:00" && $date <= "13:30:00") { ?>
							N'oubliez pas de prendre des pauses... Bonjour !
							<?php } else if($date > "13:30:00" && $date <= "18:00:00") { ?>
							Bonjour !
							<?php } else if($date > "18:00:00" && $date <= "21:00:00") { ?>
							Bonsoir !
							<?php } else if($date > "21:00:00" && $date <= "23:00:00") { ?>
							Courage, encore quelques heures !
							<?php } else { ?>
							Il fait nuit... Vous êtes toujours là ? Bienvenue !
							<?php } ?>
						</h1>
						<p>Bienvenue sur Salsabor Gestion. Que souhaitez-vous faire ?</p>
						<p>
							<a class="btn btn-primary btn-lg" href="inscription.php?status=contact"><span class="glyphicon glyphicon-user"></span> Réaliser une inscription</a>
							<a class="btn btn-primary btn-lg" href="catalogue.php"><span class="glyphicon glyphicon-th"></span> Vendre un produit</a>
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
