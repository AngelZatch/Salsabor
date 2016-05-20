<?php
session_start();
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

// Get all products with a negative amount of hours remaining

$queryIrregulars = $db->query("SELECT * FROM produits_adherents pa
								JOIN users u ON id_user_foreign = u.user_id
								JOIN produits p ON id_produit_foreign = p.produit_id
								JOIN transactions t ON id_transaction_foreign = t.id_transaction
								WHERE volume_cours < '0.00' AND est_illimite != '1'
								ORDER BY volume_cours ASC");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script>
			$(document).ready(function(){
				$("#product-modal").on("hidden.bs.modal", function(){
					console.log("Modal closed");
					$(".item-expired").remove();
					$(".item-active").remove();
				})
			})
		</script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-queen"></span> Forfaits en sur-consommation</legend>
					<p class="sub-legend"><?php echo $queryIrregulars->rowCount();?> forfaits concernés.</p>
					<div class="container-fluid irregulars-container">
						<ul class="purchase-inside-list purchase-product-list">
							<?php while($irregulars = $queryIrregulars->fetch(PDO::FETCH_ASSOC)){ ?>
							<li class="purchase-item panel-item item-overconsumed container-fluid" id="purchase-item-<?php echo $irregulars["id_produit_adherent"];?>" data-toggle='modal' data-target="#product-modal" data-argument="<?php echo $irregulars["id_produit_adherent"];?>">
								<p class="col-lg-3 panel-item-title"><?php echo $irregulars["produit_nom"];?></p>
								<p class="col-lg-3 purchase-product-owner"><?php echo $irregulars["user_prenom"]." ".$irregulars["user_nom"];?></p>
								<p class="col-lg-3 purchase-product-hours"><?php echo -1 * $irregulars["volume_cours"];?> heures en excès</p>
								<p class="col-lg-1 purchase-price align-right"><?php echo $irregulars["prix_achat"];?> €</p>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php include "inserts/modal_product.php";?>
	</body>
</html>
