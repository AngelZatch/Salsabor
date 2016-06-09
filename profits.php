<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$unique = $db->query("SELECT DISTINCT user_id FROM participations WHERE passage_date > '2015-09-01 00:00:00'");

$members = $db->query("SELECT user_id, prix_total FROM participations pr
						JOIN transactions t ON t.payeur_transaction = pr.user_id
						WHERE passage_date > '2015-09-01 00:00:00'
						AND prix_total > 150.00
						GROUP BY user_id");

$sum = $db->query("SELECT user_id, SUM(prix_total) FROM participations pr
						JOIN transactions t ON t.payeur_transaction = pr.user_id
						WHERE passage_date > '2015-09-01 00:00:00'
						GROUP BY user_id")->fetch(PDO::FETCH_ASSOC);
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Rentabilité | Salsabor</title>
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/circle-progress.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-usd"></span> Rentabilité
					</legend>
					<div id="results-list" class="container-fluid">
						<p class="sub-legend">Participations uniques</p>
						<p><?php echo $unique->rowCount();?> participations uniques depuis le 01/09/2015.</p>

						<p class="sub-legend">Adhérents (participants avec au moins 140€ de dépenses)</p>
						<p><?php echo $members->rowCount();?> adhérents avec au moins 140€ de dépenses.</p>

						<p class="sub-legend">Somme de toutes les transactions depuis le 01/09/2015</p>
						<p><?php echo $sum["SUM(prix_total)"];?> €</p>

						<p class="sub-legend">Prix moyens (produits achetés après le 01/09/2015)</p>
						<table class="table">
							<thead>
								<th>Nom</th>
								<th>Prix fixé</th>
								<th>Prix réel</th>
								<th>Participations</th>
								<th>Prix du cours moyen</th>
							</thead>
							<tbody>
								<?php $unlimited = $db->query("SELECT produit_id, produit_nom, tarif_global FROM produits WHERE est_illimite = 1 OR est_recharge = 1");

								while($product = $unlimited->fetch(PDO::FETCH_ASSOC)){
									$mean = $db->query("SELECT prix_achat, date_achat, COUNT(pr.passage_id) AS count_participations, prix_achat/COUNT(pr.passage_id) AS mean_value FROM produits_adherents pa
													JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
													JOIN participations pr ON pr.produit_adherent_id = pa.id_produit_adherent
													WHERE id_produit_foreign = $product[produit_id] AND date_achat > '2015-09-01 00:00:00'
													GROUP BY produit_adherent_id");
									$value = 0; $total_selling_price = 0; $total_participations = 0;
									while($single = $mean->fetch(PDO::FETCH_ASSOC)){
										$value += $single["mean_value"];
										$total_selling_price += $single["prix_achat"];
										$total_participations += $single["count_participations"];
									}
									$count = $mean->rowCount();
									if($count != 0){
										$mean_value = $value / $count;
										$mean_selling_price = $total_selling_price / $count;
									} else {
										$mean_value = 0.00;
										$mean_selling_price = $total_selling_price / 1;
									}
									echo "<tr>";
									echo "<td>".$product["produit_nom"]."</td>";
									echo "<td>".number_format($product["tarif_global"])." €</td>";
									echo "<td>".number_format($mean_selling_price, 2)." €</td>";
									echo "<td>".$total_participations."</td>";
									echo "<td>".number_format($mean_value, 2)." € </td>";
									echo "</tr>";
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
