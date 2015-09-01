<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$date = new DateTime('now');
$year = $date->format('Y');
$month = $date->format('m');
$day = $date->format('d');
if($day >= 1 && $day < 8){
	$maturityDay = 10;
} else if($day >= 9 && $day < 18){
	$maturityDay = 20;
} else if($day >= 19 && $day < 28){
	$maturityDay = 30;
}else{
	$maturityDay = 10;
	$month+=1;
}
$time = new DateTime($year.'-'.$month.'-'.$maturityDay);
$maturityTime = $time->format('Y-m-d');

$queryEcheances = $db->prepare("SELECT * FROM produits_echeances
										JOIN produits_adherents ON reference_achat=produits_adherents.id_transaction_foreign
										JOIN produits ON id_produit_foreign=produits.produit_id
										JOIN users ON id_user_foreign=users.user_id
										WHERE date_echeance<='$maturityTime' AND statut_banque = 0");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Echeances | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-10 main">
					<p id="current-time"></p>
					<h1 class="page-title"><span class="glyphicon glyphicon-repeat"></span> Echéances</h1>
					<p>Encaissement prévu le <?php echo $time->format('d/m/Y');?></p>
					<div class="input-group input-group-lg search-form">
						<span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span></span>
						<input type="text" id="search" class="form-control" placeholder="Tapez pour rechercher...">
					</div>
					<div id="maturities-list">
						<?php include "inserts/echeancier.php";?>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			function uploadChanges(token, value){
				var database = "produits_echeances";
				$.post("functions/update_field.php", {database, token, value}).done(function(data){
					showSuccessNotif(data);
				});
			}
			var options = {
				valueNames: ['date', 'forfait-name', 'user-name', 'montant', 'status', 'bank']
			};
			var maturitiesList = new List('maturities-list', options);
		</script>
	</body>
</html>
