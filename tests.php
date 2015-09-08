<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$produit = $db->query("SELECT * FROM produits_adherents
						JOIN produits ON id_produit_foreign=produits.produit_id
						WHERE id_produit_adherent=1")->fetch(PDO::FETCH_ASSOC);
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template - Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-sm-10 main">
					<?php
$date_now = date_create('now')->format('Y-m-d H:i:s');
	$actif = 1;
	$date_activation = $date_now;
	$date_expiration = date("Y-m-d 00:00:00", strtotime($date_activation.'+'.$produit["validite_initiale"].'DAYS'));
	$queryHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee >= ? AND date_chomee <= ?");
	$queryHoliday->bindParam(1, $date_activation);
	$queryHoliday->bindParam(2, $date_expiration);
	$queryHoliday->execute();

echo $date_expiration;
	$j = 0;

	for($i = 0; $i <= $queryHoliday->rowCount(); $i++){
		echo "Boucle";
		$exp_date = date("Y-m-d 00:00:00",strtotime($date_expiration.'+'.$i.'DAYS'));
		$checkHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee=?");
		$checkHoliday->bindParam(1, $exp_date);
		$checkHoliday->execute();
		if($checkHoliday->rowCount() != 0){
			$j++;
		}
		$totalOffset = $i + $j;
		echo $new_exp_date = date("Y-m-d 00:00:00",strtotime($date_expiration.'+'.$totalOffset.'DAYS'));
	}
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
