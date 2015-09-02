<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET["id"];
$status = $_GET["status"];

$queryTransaction = $db->prepare("SELECT * FROM transactions WHERE id_transaction=?");
$queryTransaction->bindValue(1, $data);
$queryTransaction->execute();
$transaction = $queryTransaction->fetch(PDO::FETCH_ASSOC);

$queryProduits = $db->prepare("SELECT * FROM produits_adherents JOIN produits ON id_produit_foreign=produits.produit_id JOIN users ON id_user_foreign=users.user_id WHERE id_transaction_foreign=?");
$queryProduits->bindValue(1, $data);
$queryProduits->execute();

$queryEcheances = $db->prepare("SELECT * FROM produits_echeances WHERE reference_achat=?");
$queryEcheances->bindValue(1, $data);
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Transaction <?php echo $transaction["id_transaction"];?> | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-credit-card"></span> Transaction <?php echo $transaction["id_transaction"]?></p>
					</div>
					<div class="col-lg-6">
						<div class="btn-toolbar">
							<?php if($status == "transactions"){ ?>
							<a href="transactions.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour aux transactions</a>
							<?php } else { ?>
							<a href="user_details.php?id=<?php echo $transaction["payeur_transaction"];?>&status=<?php echo $status;?>" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à l'adhérent</a>
							<?php } ?>
						</div> <!-- btn-toolbar -->
					</div>
				</div>
				<div class="col-sm-10 main">
					<ul class="nav nav-tabs">
						<li role="presentation" id="infos-toggle" class="active"><a>Détails</a></li>
						<li role="presentation" id="maturity-toggle"><a>Echéances</a></li>
					</ul>
					<section id="infos">
						<ul style="padding-left:0 !important;">
							<li class="details-list">
								<div class="col-sm-5 list-name">Transaction effectuée le</div>
								<div class="col-sm-7"><?php echo $transaction["date_achat"];?></div>
							</li>
							<li class="details-list">
								<div class="col-sm-5 list-name">Prix total</div>
								<div class="col-sm-7"><?php echo $transaction["prix_total"];?> €</div>
							</li>
							<li class="details-list">
								<div class="col-sm-5 list-name">Liste des produits</div>
								<div class="col-sm-7">
									<?php while($produits = $queryProduits->fetch(PDO::FETCH_ASSOC)){ ?>
									<p><?php echo $produits["produit_nom"];?> pour <?php echo $produits["user_prenom"]." ".$produits["user_nom"];?></p>
									<?php } ?>
								</div>
							</li>
						</ul>
					</section>
					<section id="maturity">
						<?php include "inserts/echeancier.php";?>
					</section>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/nav-tabs.js"></script>
		<script>
			function uploadChanges(token, value){
				var database = "produits_echeances";
				$.post("functions/update_field.php", {database, token, value}).done(function(data){
					showSuccessNotif(data);
				});
			}
			$(document).ready(function(){
				$(".statut-salsabor").click(function(){
					var echeance_id = $(this).parent("td").children("input[name^='echeance']").val();
					var container = $(this).parent("td");
					$.post("functions/validate_echeance.php", {echeance_id}).done(function(data){
						showSuccessNotif(data);
						container.empty();
						container.html("<span class='label label-success'>Réceptionnée</span>");
						$(".statut-salsabor").removeClass("glyphicon-download-alt");
					})
				})

				$(".statut-banque").click(function(){
					var echeance_id = $(this).parent("td").children("input[name^='echeance']").val();
					var container = $(this).parent("td");
					$.post("functions/encaisser_echeance.php", {echeance_id}).done(function(data){
						showSuccessNotif(data);
						container.empty();
						container.html("<span class='label label-success'>Encaissée</span>");
					})
				})
			})
		</script>
	</body>
</html>
