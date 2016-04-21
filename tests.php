<?php
require_once 'functions/db_connect.php';
include "functions/mails.php";
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template - Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</legend>
					<?php
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$start = $loading;
					/** CODE **/
					$master_settings = $db->query("SELECT * FROM master_settings WHERE user_id = 0")->fetch(PDO::FETCH_ASSOC);

					echo $today = date("Y-m-d H:i:s");
					echo "<br>";
					echo $expiration_limit = date("Y-m-d 23:59:59", strtotime($today.'+'.$master_settings["days_before_exp"].'DAYS'));
					echo "<br>";
					echo $maturity_limit = date("Y-m-d 23:59:59", strtotime($today.'+'.$master_settings["days_before_maturity"].'DAYS'));
					echo "<br>";
					echo $maturity_over = date("Y-m-d", strtotime($today.'-'.$master_settings["days_after_maturity"].'DAYS'));
					echo "<br>";
					echo $hour_limit = $master_settings["hours_before_exp"];
					echo "<br>";
					/*$products = $db->query("SELECT * FROM produits_adherents pa
						JOIN produits p ON pa.id_produit_foreign = p.produit_id
						WHERE pa.actif = 1 AND validite_initiale > '$hour_limit' AND est_abonnement = 0
						AND (date_expiration <= '$expiration_limit' OR (volume_cours > 0 AND volume_cours <= '$hour_limit'))");*/
					$products = $db->query("SELECT * FROM produits_adherents pa
						JOIN produits p ON pa.id_produit_foreign = p.produit_id
						WHERE pa.actif = 1 AND validite_initiale > '$hour_limit'
						AND ((date_expiration <= '$expiration_limit' OR (date_prolongee != '0000-00-00 00:00:00' AND date_prolongee <= '$expiration_limit')) OR (volume_cours > 0 AND volume_cours <= '$hour_limit' AND est_abonnement = 0))");

					while($product = $products->fetch(PDO::FETCH_ASSOC)){
						$token = "PRD-";
						$target = $product["id_produit_adherent"];
						$date = $today;
						$exp_date = max($product["date_expiration"], $product["date_prolongee"]);
						if($exp_date <= $expiration_limit){
							$token .= "NE";
							echo $token."; ".$target."; ".$date." | Date d'expiration : ".$exp_date." | Prolongée :".$product["date_prolongee"]."<br>";
							$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_date, notification_state)
								VALUES('$token', '$target', '$date', '1')");
						} else if($product["volume_cours"] > 0 && $product["volume_cours"] <= $hour_limit){
							$token .= "NH";
							echo $token."; ".$target."; ".$date." | Heures restantes : ".$product["volume_cours"]."<br>";
							$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_date, notification_state)
								VALUES('$token', '$target', '$date', '1')");
						} else {
							echo $token."; ".$target."; ".$date." | Détails : ".$product["produit_nom"].", ".$product["volume_cours"]." heures, expiration le : ".$product["date_expiration"]." (prolongée au : ".$product["date_prolongee"].")<br>";
						}
					}

					/** /CODE **/
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$finish = $loading;
					$total = round(($finish - $start), 4);
					echo "<br>Traitement effectué en ".$total." secondes";
					?>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
		</script>
	</body>
</html>
<script>
</script>
