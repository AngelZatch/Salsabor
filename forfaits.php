<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryForfaits = $db->query("SELECT * FROM produits");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Forfaits | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-credit-card"></span> Forfaits
						<a href="forfait_add.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Ajouter un forfait</a>
					</legend>
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th>Produit</th>
									<th>Volume de cours (heures)</th>
									<th>Durée de validité (jours)</th>
									<th>Tarif horaire</th>
									<th>Tarif global</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php while($produits = $queryForfaits->fetch(PDO::FETCH_ASSOC)){ ?>
								<tr>
									<td><?php echo $produits["produit_nom"];?></td>
									<td><?php echo $produits["volume_horaire"];?></td>
									<td><?php echo $produits["validite_initiale"];?></td>
									<td><?php echo $produits["tarif_horaire"];?> €</td>
									<td><?php echo $produits["tarif_global"];?> €</td>
									<td><a href="forfait_details.php?id=<?php echo $produits["produit_id"];?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Détails...</a></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
