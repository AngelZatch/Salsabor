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
		<?php include "styles.php";?>
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
				<div class="col-sm-10 main"><?php
$today = date_create('now')->format('Y-m-d H:i:s');
// Si ce même code a été passé il y a moins de 20 minutes, le passage est refusé
echo $tenPrevious = date('Y-m-d H:i:s', strtotime($today.'-10MINUTES'));
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
