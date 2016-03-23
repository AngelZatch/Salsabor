<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// On obtient les détails de l'adhérent
$queryDetails = $db->prepare('SELECT * FROM users WHERE user_id=?');
$queryDetails->bindValue(1, $data);
$queryDetails->execute();
$details = $queryDetails->fetch(PDO::FETCH_ASSOC);

// On obtient l'historique de ses réservations
$queryResa = $db->prepare('SELECT * FROM reservations JOIN users ON reservation_personne=users.user_id JOIN prestations ON type_prestation=prestations_id JOIN salle ON reservation_salle=salle.salle_id WHERE reservation_personne=?');
$queryResa->bindValue(1, $data);
$queryResa->execute();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Editer - <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-user"></span> <?php echo $details["user_prenom"]." ".$details["user_nom"];?> - Réservations</legend>
					<ul class="nav nav-tabs">
						<li role="presentation"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/historique">Cours suivis</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<?php if($details["est_professeur"] == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<section id="resa">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Plage horaire</th>
									<th>Lieu</th>
									<th>Activité</th>
									<th>Prix de la réservation</th>
								</tr>
							</thead>
							<tbody>
								<?php while($reservations = $queryResa->fetch(PDO::FETCH_ASSOC)){ ?>
								<tr>
									<td>Le <?php echo date_create($reservations["reservation_start"])->format('d/m/Y \d\e H\hi');?> à <?php echo date_create($reservations["reservation_end"])->format('H\hi');?></td>
									<td><?php echo $reservations["salle_name"];?></td>
									<td><?php echo $reservations["prestations_name"];?></td>
									<td><?php echo $reservations["reservation_prix"];?> €</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</section>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
