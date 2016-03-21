<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// On obtient les détails de l'adhérent
$queryDetails = $db->prepare('SELECT * FROM users WHERE user_id=?');
$queryDetails->bindValue(1, $data);
$queryDetails->execute();
$details = $queryDetails->fetch(PDO::FETCH_ASSOC);

// On obtient l'historique de ses cours
/*$queryHistoryRecus = $db->prepare('SELECT * FROM cours_participants
							JOIN cours ON cours_id_foreign=cours.cours_id
							JOIN niveau ON cours.cours_niveau=niveau.niveau_id
							JOIN salle ON cours.cours_salle=salle.salle_id
							JOIN produits_adherents ON produit_adherent_id=produits_adherents.id_produit_adherent
							JOIN produits ON id_produit_foreign=produits.produit_id
							WHERE eleve_id_foreign=?');*/
$queryHistoryRecus = $db->prepare('SELECT *, pa.date_activation AS produit_adherent_activation,
							IF(date_prolongee IS NOT NULL, date_prolongee,
								IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
								) AS produit_validity
							FROM cours_participants cp
							JOIN cours c ON cp.cours_id_foreign=c.cours_id
							LEFT JOIN produits_adherents pa ON cp.produit_adherent_id=pa.id_produit_adherent
							LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
							LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
							WHERE eleve_id_foreign=?
							ORDER BY cours_start DESC');
$queryHistoryRecus->bindValue(1, $data);
$queryHistoryRecus->execute();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Cours suivis par <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-user"></span> <?php echo $details["user_prenom"]." ".$details["user_nom"];?> - Historique de cours</p>
					</div>
				</div>
				<div class="col-sm-10 main">
					<ul class="nav nav-tabs">
						<li role="presentation"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/historique">Cours suivis</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<?php if($details["est_professeur"] == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<div class="container-fluid participations-list-container">
						<ul class="participations-list">
							<?php while($history = $queryHistoryRecus->fetch(PDO::FETCH_ASSOC)){
	$date = date_create($history["cours_start"])->format("d/m/Y");
	$hour_start = date_create($history["cours_start"])->format("H:i");
	$hour_end = date_create($history["cours_end"])->format("H:i");?>
							<li class="product-participation <?php echo ($history["produit_adherent_id"]==null)?"participation-over":"participation-valid";?> container-fluid" data-argument="<?php echo $history["id"];?>" id="participation-<?php echo $history["id"];?>">
								<div class="col-lg-4">
									<p class="col-lg-12 session-title"><?php echo $history["cours_intitule"];?></p>
									<p class="col-lg-12 session-hours"><?php echo $date." : ".$hour_start." - ".$hour_end;?></p>
								</div>
								<div class="col-lg-8">
									<?php if($history["produit_adherent_id"] == null){ ?>
									<p class="col-lg-12 session-title">Pas de produit associé</p>
									<?php } else {
		$achat = date_create($history["date_achat"])->format("d/m/Y");
		$activation_date = date_create($history["produit_adherent_activation"])->format("d/m/Y");
		$expiration_date = date_create($history["produit_validity"])->format("d/m/Y");
									?>
									<p class="col-lg-12 session-title"><?php echo $history["produit_nom"];?></p>
									<p class="col-lg-12 session-hours">Acheté le <?php echo $achat;?> / Valide du <?php echo $activation_date;?> au <?php echo $expiration_date;?> </p>
									<?php } ?>
								</div>
							</li>

							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
