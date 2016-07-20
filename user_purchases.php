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
		<script src="assets/js/bootstrap-slider.min.js"></script>
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
							<a class="panel-heading-container" onClick="fetchPurchase('<?php echo $achats["id_transaction"];?>')">
								<div class="panel-heading container-fluid">
									<p class="purchase-id col-lg-5">Transaction <?php echo $achats["id_transaction"];?></p>
									<p class="col-lg-3">Contient <?php echo $productQty;?> produit(s)</p>
									<p class="purchase-sub col-lg-4">Effectuée le <?php echo date_create($achats["date_achat"])->format('d/m/Y');?> - <?php echo $achats["prix_total"];?> €</p>
									<!--<button class="btn btn-default fetch-purchase" onClick="fetchPurchase('<?php echo $achats["id_transaction"];?>')">Détails</button>-->
									<!--<a href="purchase_details.php?id=<?php echo $achats["id_transaction"];?>&status=<?php echo $status;?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Détails...</a>-->
								</div>
							</a>
							<div class="panel-body collapse" id="body-purchase-<?php echo $achats["id_transaction"];?>">
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php include "inserts/modal_product.php";?>
		<?php include "inserts/modal_maturity.php";?>
		<script>
			/** Check reception and banking of maturities **/
			function checkReception(maturity_id){
				//console.log(maturity_id);
				$.post("functions/check_maturity.php", {maturity_id : maturity_id}).done(function(data){
					/*console.log(data);*/
					$("#date-reception-"+maturity_id).text(moment(data).format("DD/MM/YYYY"));
					$("#icon-reception-"+maturity_id).addClass("icon-success");
					document.getElementById("icon-reception-"+maturity_id).onclick = function(){ uncheckReception(maturity_id); };
				})
			}
			function uncheckReception(maturity_id){
				//console.log(maturity_id);
				$.post("functions/uncheck_maturity.php", {maturity_id : maturity_id}).done(function(data){
					if(data == '2'){
						$("#date-reception-"+maturity_id).text("En retard");
					} else {
						$("#date-reception-"+maturity_id).text("En attente");
					}
					$("#icon-reception-"+maturity_id).removeClass("icon-success");
					document.getElementById("icon-reception-"+maturity_id).onclick = function(){ checkReception(maturity_id); };
				})
			}
			function checkBank(maturity_id){
				/*console.log(maturity_id);*/
				$.post("functions/check_bank.php", {maturity_id : maturity_id}).done(function(data){
					$("#date-bank-"+maturity_id).text(moment(data).format("DD/MM/YYYY"));
					$("#icon-bank-"+maturity_id).addClass("icon-success");
					document.getElementById("icon-bank-"+maturity_id).onclick = function(){ uncheckBank(maturity_id); };
				})
			}
			function uncheckBank(maturity_id){
				/*console.log(maturity_id);*/
				$.post("functions/uncheck_bank.php", {maturity_id : maturity_id}).done(function(data){
					$("#date-bank-"+maturity_id).text("En attente");
					$("#icon-bank-"+maturity_id).removeClass("icon-success");
					document.getElementById("icon-bank-"+maturity_id).onclick = function(){ checkBank(maturity_id); };
				})
			}
		</script>
	</body>
</html>
