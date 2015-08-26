<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET["id"];
$status = $_GET["status"];

$queryTransaction = $db->prepare("SELECT * FROM transactions WHERE id_transaction=?");
$queryTransaction->bindValue(1, $data);
$queryTransaction->execute();
$transaction = $queryTransaction->fetch(PDO::FETCH_ASSOC);

$queryEcheances = $db->prepare("SELECT * FROM produits_echeances WHERE reference_achat=?");
$queryEcheances->bindValue(1, $data);
$queryEcheances->execute();

$queryProduits = $db->prepare("SELECT * FROM produits_adherents JOIN produits ON id_produit_foreign=produits.produit_id JOIN users ON id_user_foreign=users.user_id WHERE id_transaction_foreign=?");
$queryProduits->bindValue(1, $data);
$queryProduits->execute();
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
				<div class="col-sm-10 main">
					<div class="btn-toolbar" id="top-page-buttons">
						<a href="user_details.php?id=<?php echo $transaction["payeur_transaction"];?>&status=<?php echo $status;?>" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à l'adhérent</a>
					</div> <!-- btn-toolbar -->
					<h1 class="page-title"><span class="glyphicon glyphicon-credit-card"></span> Transaction <?php echo $transaction["id_transaction"]?></h1>
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
						<table class="table">
							<thead>
								<tr>
									<th>Date de l'échéance</th>
									<th>Montant de l'échéance</th>
									<th>Méthode de paiement</th>
									<th>Statut Salsabor</th>
									<th>Statut Banque</th>
								</tr>
							</thead>
							<tbody>
								<?php while($echeances = $queryEcheances->fetch(PDO::FETCH_ASSOC)){
	switch($echeances["echeance_effectuee"]){
		case 0:
		$status = "En attente";
		$statusClass = "default";
		break;

		case 1:
		$status = "Réceptionnée";
		$statusClass = "success";
		break;

		case 2:
		$status = "En retard";
		$statusClass = "danger";
		break;
	} ?>
								<tr>
									<td><?php echo date_create($echeances["date_echeance"])->format('d/m/Y');?></td>
									<td><?php echo $echeances["montant"];?> €</td>
									<td><?php echo $echeances["methode_paiement"];?></td>
									<td class="status">
										<?php if($status == "Réceptionnée"){ ?>
										<span class="label label-<?php echo $statusClass;?>"><?php echo $status;?></span>
										<?php } else { ?>
										<span class="label label-info"><?php echo $status;?></span>
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
					</section>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/nav-tabs.js"></script>
	</body>
</html>
