<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryTransactions = $db->query("SELECT * FROM transactions JOIN users ON payeur_transaction=users.user_id");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Transactions | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-piggy-bank"></span> Transactions</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-sm-10 main">
					<div id="echeances-list">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Transaction</th>
									<th>Date</th>
									<th>Payeur</th>
									<th>Montant</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php while($transactions = $queryTransactions->fetch(PDO::FETCH_ASSOC)){ ?>
								<tr onclick="window.location.href='transaction_details.php?id=<?php echo $transactions["id_transaction"];?>&status=transactions'" style="cursor:default;">
									<td><?php echo $transactions["id_transaction"];?></td>
									<td><?php echo $transactions["date_achat"];?></td>
									<td><?php echo $transactions["user_prenom"]." ".$transactions["user_nom"];?></td>
									<td><?php echo $transactions["prix_total"];?></td>
									<td><a href="transaction_details.php?id=<?php echo $transactions["id_transaction"];?>&status=transactions" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Détails...</a></td>
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
