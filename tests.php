<?php
session_start();
require_once 'functions/db_connect.php';
include "functions/mails.php";
include "functions/tools.php";
include "functions/post_task.php";
include "functions/attach_tag.php";
include "functions/activate_product.php";
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
					/** CODE **/
					$noCards = $db->query("SELECT user_id FROM users u WHERE actif = 1")->fetchAll(PDO::FETCH_COLUMN);

					?>
					<pre>
						<?php
foreach($noCards as $user){
	$membership_cards = $db->query("SELECT id_produit_adherent, pa.actif, date_achat, date_expiration
							FROM produits_adherents pa
							JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
							JOIN produits p ON pa.id_produit_foreign = p.product_id
							WHERE id_user_foreign = '$user' AND product_name = 'Adhésion Annuelle' ORDER BY id_produit_adherent ASC")->fetchAll();
	echo "User : ".$user."<br>";
	if(sizeof($membership_cards) == 0){
		echo "No active card<br>";
	} else {
		echo "Cards : ".sizeof($membership_cards)."<br>";
		// Resetting loop variables
		$active_card = false;
		unset($next_activation_date);
		// Test purposes
		foreach($membership_cards as $card){
			print_r($card);
			if($card["actif"] == 0 && !$active_card){
				if(isset($next_activation_date))
					$activation_date = $next_activation_date;
				else
					$activation_date = $card["date_achat"];
				echo "This card needs to be activated with the set date : ".$activation_date."<br>";
				activateProduct($db, $card["id_produit_adherent"], $activation_date);
				$active_card = true;
			}
			if($card["actif"] == 1){ // If the card is active, no need to search for other cards to activate
				$active_card = true;
			}
			if($card["actif"] == 2){ // If the card has expired, the next one will have to be activated with this one's expiration date in mind for continued activation.
				echo "Card expired<br>";
				$next_activation_date = $card["date_expiration"];
			}
		}
		if(!$active_card){
			echo "No active card<br>";
		}
		echo "<br>";
	}
}
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
