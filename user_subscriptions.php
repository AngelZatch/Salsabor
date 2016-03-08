<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// On obtient les détails de l'adhérent
$queryDetails = $db->prepare('SELECT * FROM users WHERE user_id=?');
$queryDetails->bindValue(1, $data);
$queryDetails->execute();
$details = $queryDetails->fetch(PDO::FETCH_ASSOC);

// On obtient l'historique de ses forfaits
$queryForfaits = $db->prepare('SELECT *, pa.date_activation AS produit_adherent_activation, pa.actif AS produit_adherent_actif,
IF(date_prolongee IS NULL, date_expiration, date_prolongee) AS produit_validity
								FROM produits_adherents pa
								JOIN users u ON id_user_foreign=u.user_id
								JOIN produits p ON id_produit_foreign=p.produit_id
								LEFT OUTER JOIN transactions t
									ON id_transaction_foreign=t.id_transaction
									AND t.id_transaction IS NOT NULL
								WHERE id_user_foreign=?
								ORDER BY produit_adherent_activation DESC');
$queryForfaits->bindValue(1, $data);
$queryForfaits->execute();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Editer - <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-user"></span> <?php echo $details["user_prenom"]." ".$details["user_nom"];?> - Abonnements</p>
					</div>
				</div>
				<div class="col-sm-10 main">
					<ul class="nav nav-tabs">
						<li role="presentation"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/historique">Cours suivis</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<?php if($details["est_professeur"] == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<div class="container-fluid purchase-product-list-container">
						<ul class="purchase-inside-list purchase-product-list">
							<?php while($forfaits = $queryForfaits->fetch(PDO::FETCH_ASSOC)){
	$date_activation = date_create($forfaits["produit_adherent_activation"]);
	$date_expiration = date_create($forfaits["produit_validity"]);
	$today = date('Y-m-d');
	if($forfaits["produit_adherent_actif"] == '0'){
		$item_class = "item-pending";
	} else if($forfaits["produit_adherent_actif"] == '2') {
		$item_class = "item-expired";
	} else {
		if($date_activation->format('Y-m-d') > $today){
			$item_class = "item-near-activation";
		} else {
			$item_class = "item-active";
		}
	}?>
							<li class="purchase-item <?php echo $item_class;?> container-fluid" id="purchase-item-<?php echo $forfaits["id_produit_adherent"];?>" data-toggle='modal' data-target='#product-modal' data-argument="<?php echo $forfaits["id_produit_adherent"];?>">
								<p class="col-lg-3 purchase-product-name"><?php echo $forfaits["produit_nom"];?></p>
								<p class="col-lg-3 purchase-product-validity">
									<?php if($forfaits["produit_adherent_actif"] == '0'){
		echo "En attente";
	} else if($forfaits["produit_adherent_actif"] == '2'){
		echo "Expiré le ".date_create($forfaits["date_expiration"])->format('d/m/Y');
	} else {
		echo "Valide du <span>".$date_activation->format('d/m/Y')."</span> au <span>".$date_expiration->format('d/m/Y')."</span>";
	}?>
								</p>
								<p class="col-lg-3 purchase-product-hours"><?php echo $forfaits["volume_cours"];?> heures restantes</p>
								<p class="col-lg-1 purchase-price align-right"><?php echo $forfaits["prix_achat"];?> €</p>
							</li>
							<?php } ?>
						</ul>
					</div>
					<a href="catalogue.php?user=<?php echo $details["user_id"];?>" class="btn btn-primary btn-block">Acheter un nouveau produit pour cet adhérent</a>
				</div>
			</div>
		</div>
		<?php include "inserts/modal_product.php";?>
	</body>
</html>
