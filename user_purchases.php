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

//Enfin, on obtient l'historique de tous les achats (mêmes les forfaits d'autres personnes)
$queryAchats = $db->query("SELECT * FROM transactions
						WHERE id_transaction IN (SELECT id_transaction_foreign FROM produits_adherents WHERE id_user_foreign = '$data') OR payeur_transaction='$data'
						ORDER BY date_achat DESC");

$queryTransactions = $db->query("SELECT * FROM produits_adherents WHERE id_user_foreign = '$data'");

$is_teacher = $db->query("SELECT * FROM assoc_user_tags ur
								JOIN tags_user tu ON tu.rank_id = ur.tag_id_foreign
								WHERE rank_name = 'Professeur' AND user_id_foreign = '$data'")->rowCount();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Achats - <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/bootstrap-slider.min.css">
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script src="assets/js/maturities.js"></script>
		<script src="assets/js/bootstrap-slider.min.js"></script>
		<script src="assets/js/circle-progress.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<ul class="nav nav-tabs">
						<li role="presentation" class="visible-xs-block"><a href="user/<?php echo $data;?>">Infos perso</a></li>
						<li role="presentation" class="hidden-xs"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/historique">Participations</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/taches">Tâches</a></li>
						<?php if($is_teacher == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<div>
						<?php while($achats = $queryAchats->fetch(PDO::FETCH_ASSOC)){
	$productQty = $db->query("SELECT id_produit_adherent FROM produits_adherents WHERE id_transaction_foreign='$achats[id_transaction]'")->rowCount();?>
						<div class="panel panel-purchase" id="purchase-<?php echo $achats["id_transaction"];?>">
							<div class="panel-heading container-fluid" onClick="displayPurchase('<?php echo $achats["id_transaction"];?>')">
								<p class="purchase-id col-xs-4">Transaction <?php echo $achats["id_transaction"];?></p>
								<p class="col-xs-3"><?php echo $productQty;?> produit(s)</p>
								<p class="purchase-sub col-xs-3"><?php echo date_create($achats["date_achat"])->format('d/m/Y');?> - <span id="price-<?php echo $achats["id_transaction"];?>"><?php echo $achats["prix_total"];?></span> €</p>
								<span class="glyphicon glyphicon-briefcase glyphicon-button glyphicon-button-alt glyphicon-button-big create-contract col-xs-1" id="create-contract-<?php echo $achats["id_transaction"];?>" data-transaction="<?php echo $achats["id_transaction"];?>"title="Afficher le contrat"></span>
								<span class="glyphicon glyphicon-file glyphicon-button glyphicon-button-alt glyphicon-button-big create-invoice col-xs-1" id="create-invoice-<?php echo $achats["id_transaction"];?>" data-transaction="<?php echo $achats["id_transaction"];?>" title="Afficher la facture"></span>
							</div>
							<div class="panel-body collapse" id="body-purchase-<?php echo $achats["id_transaction"];?>">
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php include "inserts/modal_product.php";?>
		<?php include "inserts/sub_modal_product.php";?>
		<?php include "inserts/edit_modal.php";?>
		<script>
			$(document).ready(function(){
				var m, re = /purchase-([a-z0-9]+)/i;
				if((m = re.exec(top.location.hash)) !== null){
					var target_transaction = m[1];
					$("#purchase-"+target_transaction+">div").click();
				}
			})
			$(".create-invoice").click(function(e){
				e.stopPropagation();
				var transaction_id = document.getElementById($(this).attr("id")).dataset.transaction;
				window.open("create_invoice.php?transaction="+transaction_id, "_blank", "location=yes,height=570,width=520,scrollbars=yes,status=yes");
			})
			$(".create-contract").click(function(e){
				e.stopPropagation();
				var transaction_id = document.getElementById($(this).attr("id")).dataset.transaction;
				window.open("create_contract.php?transaction="+transaction_id, "_blank", "location=yes,height=570,width=520,scrollbars=yes,status=yes");
			})
		</script>
	</body>
</html>
