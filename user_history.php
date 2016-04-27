<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// User details
$details = $db->query("SELECT * FROM users WHERE user_id='$data'")->fetch(PDO::FETCH_ASSOC);

$queryHistoryRecus = $db->prepare('SELECT *, pa.date_activation AS produit_adherent_activation,
							IF(date_prolongee IS NOT NULL, date_prolongee,
								IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
								) AS produit_validity
							FROM participations pr
							LEFT JOIN cours c ON pr.cours_id = c.cours_id
							LEFT JOIN produits_adherents pa ON pr.produit_adherent_id = pa.id_produit_adherent
							LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
							LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
							WHERE user_id = ?
							ORDER BY passage_date DESC');
$queryHistoryRecus->bindValue(1, $data);
$queryHistoryRecus->execute();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Participations de <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script src="assets/js/records.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<legend><span class="glyphicon glyphicon-user"></span> Participations</legend>
					<ul class="nav nav-tabs">
						<li role="presentation"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/historique">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<?php if($details["est_professeur"] == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<div class="container-fluid participations-list-container">
						<p class="col-md-4"><span id="total-count"></span> Participations</p>
						<p class="col-md-4"><span id="valid-count"></span> Participations valides</p>
						<p class="col-md-4"><span id="over-count"></span> Participations hors forfait</p>
						<button class='btn btn-default btn-modal btn-link-all' id='link-all' onclick='linkAll()' title='Délier tous les cours hors forfait'><span class='glyphicon glyphicon-arrow-right'></span> Associer toutes les participations irrégulières</button>
						<ul class="participations-list">
							<?php while($history = $queryHistoryRecus->fetch(PDO::FETCH_ASSOC)){
	$date = date_create($history["cours_start"])->format("d/m/Y");
	$hour_start = date_create($history["cours_start"])->format("H:i");
	$hour_end = date_create($history["cours_end"])->format("H:i");
							switch($history["status"]){
								case '0':
									$status = "status-pre-success";
									break;

								case '2':
									$status = ($history["produit_adherent_id"]==null||$history["produit_adherent_id"]==0)?"status-over":"status-success";
									break;

								case "3":
									$status = "status-over";
									break;
							}?>
							<li class="product-participation <?php echo $status;?> container-fluid" data-argument="<?php echo $history["passage_id"];?>" id="participation-<?php echo $history["passage_id"];?>">
								<div class="col-lg-4">
									<p class="col-lg-12 session-title">
										<?php if($history["cours_intitule"]){
		echo $history["cours_intitule"];
	} else {
		echo "Pas de cours associé";
	}?>
									</p>
									<p class="col-lg-12 session-hours"><?php if($history["cours_intitule"]){
		echo $date." : ".$hour_start." - ".$hour_end;
	} else {
		echo "Enregistrée le ".date_create($history["passage_date"])->format("d/m/Y à H:i:s");
	}?></p>
								</div>
								<div class="col-lg-8">
									<?php if($history["produit_adherent_id"] == null || $history["produit_adherent_id"] == 0){ ?>
									<p class="col-lg-12 session-title">Pas de produit associé</p>
									<p class="col-lg-12 session-hours">Cliquer pour chercher un produit à associer</p>
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
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$(document).ready(function(){
				$("#total-count").text($(".product-participation").length);
				$("#valid-count").text($(".status-success").length);
				$("#over-count").text($(".status-over").length);
			})
		</script>
	</body>
</html>
