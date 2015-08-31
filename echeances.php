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

$queryEcheances = $db->query("SELECT * FROM produits_echeances
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
						<table class="table">
							<thead>
								<tr>
									<th>Date <span class="glyphicon glyphicon-sort sort" data-sort="date"></span></th>
									<th>Transaction associée <span class="glyphicon glyphicon-sort sort" data-sort="forfait-name"></span></th>
									<th>Détenteur <span class="glyphicon glyphicon-sort sort" data-sort="user-name"></span></th>
									<th>Montant <span class="glyphicon glyphicon-sort sort" data-sort="montant"></span></th>
									<th>Statut Salsabor <span class="glyphicon glyphicon-sort sort" data-sort="status"></span></th>
									<th>Statut Bancaire <span class="glyphicon glyphicon-sort sort" data-sort="bank"></span></th>
								</tr>
							</thead>
							<tbody id="filter-enabled" class="list">
								<?php while($echeances = $queryEcheances->fetch(PDO::FETCH_ASSOC)) {
	switch($echeances["echeance_effectuee"]){
		case 0:
		$status = "En attente";
		$statusClass = "info";
		break;

		case 1:
		$status = "Réceptionnée";
		$statusClass = "success";
		break;

		case 2:
		$status = "En retard";
		$statusClass = "danger";
		break;
	}?>
								<tr>
									<td class="date"><?php echo date_create($echeances["date_echeance"])->format('d/m/Y');?></td>
									<td class="forfait-name"><a href="transaction_details.php?id=<?php echo $echeances["id_transaction_foreign"];?>"><?php echo $echeances["id_transaction_foreign"];?></a></td>
									<td class="user-name"><a href="user_details.php?id=<?php echo $echeances["user_id"];?>&status=echeances"><?php echo $echeances["user_prenom"]." ".$echeances["user_nom"]." (".$echeances["telephone"].")";?></a></td>
									<td class="montant"><?php echo $echeances["montant"];?> €</td>
									<td class="status">
										<?php if($status == "Réceptionnée"){ ?>
										<span class="label label-<?php echo $statusClass;?>"><?php echo $status;?></span>
										<?php } else { ?>
										<span class="label label-<?php echo $statusClass;?>"><?php echo $status;?></span>
										<button class="btn btn-default statut-salsabor"><span class="glyphicon glyphicon-download-alt"></span> Recevoir</button>
										<?php } ?>
										<input type="hidden" name="echeance-id" value="<?php echo $echeances["id_echeance"];?>">
									</td>
									<td class="bank">
										<?php if($echeances["statut_banque"] == '1'){ ?>
										<span class="label label-success">Encaissée</span>
										<?php } else { ?>
										<span class="label label-info">Dépôt à venir</span>
										<button class="btn btn-default statut-banque"><span class="glyphicon glyphicon-download-alt"></span> Encaisser</button>
										<?php } ?>
										<input type="hidden" name="echeance-id" value="<?php echo $echeances["id_echeance"];?>">
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				$(".statut-salsabor").click(function(){
					var echeance_id = $(this).parents("td").children("input[name^='echeance']").val();
					var container = $(this).parents("td");
					$.post("functions/validate_echeance.php", {echeance_id}).done(function(data){
						showSuccessNotif(data);
						container.empty();
						container.html("<span class='label label-success'>Réceptionnée</span>");
					})
				})

				$(".statut-banque").click(function(){
					var echeance_id = $(this).parents("td").children("input[name^='echeance']").val();
					var container = $(this).parents("td");
					$.post("functions/encaisser_echeance.php", {echeance_id}).done(function(data){
						showSuccessNotif(data);
						container.empty();
						container.html("<span class='label label-success'>Encaissée</span>");
					})
				})
			})

			var options = {
				valueNames: ['date', 'forfait-name', 'user-name', 'montant', 'status', 'bank']
			};
			var maturitiesList = new List('maturities-list', options);
		</script>
	</body>
</html>
