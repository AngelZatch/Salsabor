<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// User details
$details = $db->query("SELECT * FROM users u
						WHERE user_id='$data'")->fetch(PDO::FETCH_ASSOC);

$details["count"] = $db->query("SELECT * FROM tasks
					WHERE ((task_token LIKE '%USR%' AND task_target = '$data')
					OR (task_token LIKE '%PRD%' AND task_target IN (SELECT id_produit_adherent FROM produits_adherents WHERE id_user_foreign = '$data'))
					OR (task_token LIKE '%TRA%' AND task_target IN (SELECT id_transaction FROM transactions WHERE payeur_transaction = '$data')))
						AND task_state = 0")->rowCount();

// On obtient l'historique de ses forfaits
$queryForfaits = $db->prepare('SELECT *, pa.date_activation AS produit_adherent_activation, pa.actif AS produit_adherent_actif,
								IF(date_prolongee IS NOT NULL, date_prolongee,
									IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
									) AS produit_validity
								FROM produits_adherents pa
								JOIN users u ON id_user_foreign=u.user_id
								JOIN produits p ON id_produit_foreign=p.produit_id
								LEFT OUTER JOIN transactions t
									ON id_transaction_foreign=t.id_transaction
									AND t.id_transaction IS NOT NULL
								WHERE id_user_foreign=?
								ORDER BY
									date_achat DESC');
$queryForfaits->bindValue(1, $data);
$queryForfaits->execute();

$is_teacher = $db->query("SELECT * FROM user_ranks ur
								JOIN tags_user tu ON tu.rank_id = ur.rank_id_foreign
								WHERE rank_name = 'Professeur' AND user_id_foreign = '$data'")->rowCount();
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
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<legend><span class="glyphicon glyphicon-user"></span> Abonnements</legend>
					<ul class="nav nav-tabs">
						<li role="presentation"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/historique">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/taches">Tâches</a></li>
						<?php if($is_teacher == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<div class="container-fluid purchase-product-list-container">
						<ul class="purchase-inside-list purchase-product-list">
							<?php while($forfaits = $queryForfaits->fetch(PDO::FETCH_ASSOC)){
	$date_activation = date_create($forfaits["produit_adherent_activation"]);
	$date_expiration = "-";
	if($forfaits["produit_validity"] != null){
		$date_expiration = date_create($forfaits["produit_validity"])->format('d/m/Y');
	}
	$today = date('Y-m-d');
	if($forfaits["volume_cours"] < '0' && $forfaits["est_illimite"] != '1'){
		$item_class = "item-overused";
	} else {
		if($forfaits["produit_adherent_actif"] == '0'){
			$item_class = "item-pending";
		} else if($forfaits["produit_adherent_actif"] == '2') {
			$item_class = "item-expired";
		} else {
			$item_class = "item-active";
		}
	}?>
							<li class="purchase-item panel-item <?php echo $item_class;?> container-fluid" id="purchase-item-<?php echo $forfaits["id_produit_adherent"];?>" data-toggle='modal' data-target='#product-modal' data-argument="<?php echo $forfaits["id_produit_adherent"];?>">
								<p class="col-lg-3 panel-item-title"><?php echo $forfaits["produit_nom"];?></p>
								<p class="col-lg-3 purchase-product-validity">
									<?php if($forfaits["produit_adherent_actif"] == '0'){
		echo "En attente";
	} else if($forfaits["produit_adherent_actif"] == '2'){
		echo "Expiré le ".$date_expiration;
	} else {
		echo "Valide du <span>".$date_activation->format('d/m/Y')."</span> au <span>".$date_expiration."</span>";
	}?>
								</p>
								<p class="col-lg-3 purchase-product-hours">
									<?php if($forfaits["est_illimite"] == "0" && $forfaits["est_abonnement"] == "0"){
		if($forfaits["volume_cours"] < 0){
			echo -1 * $forfaits["volume_cours"]." heures en excès";
		} else {
			echo 1 * $forfaits["volume_cours"]." heures restantes";
		}
	}?>
								</p>
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
