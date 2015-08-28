<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryTransactions = $db->prepare("SELECT * FROM transactions");
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
				<div class="col-sm-10 main">
					<p id="current-time"></p>
					<h1 class="page-title"><span class="glyphicon glyphicon-piggy-bank"></span> Transactions</h1>
					<div id="echeances-list">
						<table class="table">
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
								<tr>
									<td><?php echo $transactions["id_transaction"];?></td>
									<td><?php echo $transactions["date_achat"];?></td>
									<td></td>
									<td><?php echo $transactions["prix_total"];?></td>
									<td></td>
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
