<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

// Get all products with a negative amount of hours remaining

$queryIrregulars = $db->query("SELECT * FROM produits_adherents pa
								JOIN users u ON id_user_foreign = u.user_id
								JOIN produits p ON id_produit_foreign = p.produit_id
								WHERE volume_cours < '0.00'
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
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-pawn"></span> Forfaits en sur-consommation</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-sm-10 main">
					<div class="container-fluid irregulars-container">
						<ul class="purchase-inside-list purchase-product-list">
							<?php while($irregulars = $queryIrregulars->fetch(PDO::FETCH_ASSOC)){ ?>
							<li class="purchase-item item-overconsumed container-fluid" id="purchase-item-<?php echo $irregulars["id_produit_adherent"];?>" data-toggle='modal' data-target="#product-modal" data-argument="<?php echo $irregulars["id_produit_adherent"];?>">
								<p class="col-lg-3 purchase-product-name"><?php echo $irregulars["produit_nom"];?></p>
								<p class="col-lg-3 purchase-product-hours"><?php echo -1 * $irregulars["volume_cours"];?> heures sur-consommées</p>
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
