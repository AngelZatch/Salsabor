<?php
session_start();
require_once 'functions/db_connect.php';
include "functions/mails.php";
include "functions/tools.php";
include "functions/post_task.php";
include "functions/attach_tag.php";
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
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</legend>
					<?php
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$start = $loading;
					$searchTerms = "An";
					$location = 2;
					/** CODE **/
					$product_id = 7031;

					$query = "SELECT *, pa.actif AS produit_adherent_actif, pa.date_activation AS produit_adherent_activation, CONCAT(user_prenom, ' ', user_nom) AS user, user_id, date_prolongee, date_fin_utilisation, date_expiration
					FROM produits_adherents pa
					JOIN produits p
						ON pa.id_produit_foreign = p.product_id
					LEFT JOIN transactions t
						ON pa.id_transaction_foreign = t.id_transaction
					LEFT JOIN users u
						ON pa.id_user_foreign = u.user_id";
					$query .= " WHERE id_produit_adherent = '$product_id'";
					$query .= " ORDER BY prix_achat DESC";
					$load = $db->query($query);

					$count = $load->rowCount();
					$products_list = array();
					while($product = $load->fetch(PDO::FETCH_ASSOC)){
						$p = array(
							"id" => $product["id_produit_adherent"],
							"recipient" => $product["id_user_foreign"],
							"transaction_id" => $product["id_transaction_foreign"],
							"product_name" => $product["product_name"],
							"activation" => $product["produit_adherent_activation"],
							"expiration" => max($product["date_prolongee"], $product["date_expiration"]),
							"usage_date" => $product["date_fin_utilisation"],
							"remaining_hours" => $product["volume_cours"],
							"price" => $product["prix_achat"],
							"product_size" => $product["product_size"],
							"user" => (isset($product["user"]))?$product["user"]:"Pas d'utilisateur",
							"status" => $product["produit_adherent_actif"]
						);
						array_push($products_list, $p);
					}
					?>
					<pre>
						<?php
print_r($products_list);
print_r($products_list[0]);
?>
					</pre>

					<?php
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
