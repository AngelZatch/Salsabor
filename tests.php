<?php
require_once 'functions/db_connect.php';
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
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-lg-10 col-lg-offset-2 main">
					<?php
					$eleve = 10507;
					$product_id = "";
					$cours_id = 2819;
					$cours_name = $db->query("SELECT cours_intitule FROM cours WHERE cours_id = '$cours_id'")->fetch(PDO::FETCH_COLUMN);
					/*echo $cours_name;*/
					if($product_id == ""){ // If the product has not been manually set
						// CASE WHERE THIS IS NOT A PRIVATE SESSION
						if(preg_match("/jazz/i", $cours_name, $matches) || preg_match("/pilates/i", $cours_name, $matches) || preg_match("/particulier/i", $cours_name, $matches)){ // Search for specific Jazz, Pilates or private sessions
							/*echo $matches[0];*/
							$checkSpecific = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$eleve'
									AND produit_nom LIKE '%$matches[0]%'
									AND pa.actif != '2'
									ORDER BY date_achat ASC");
							if($checkSpecific->rowCount() > 0){
								$product = $checkSpecific->fetch(PDO::FETCH_ASSOC);
							} else { // First, we search for any freebies
								$checkInvitation = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$eleve'
									AND produit_nom = 'Invitation'
									AND pa.actif = '0'
									ORDER BY date_achat ASC");
								if($checkInvitation->rowCount() > 0){ // If there are freebies still available, we take the first one.
									$product = $checkInvitation->fetch(PDO::FETCH_ASSOC);
								} else { // If no freebies, we look for every currently active products.
									$checkActive = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$eleve'
									AND produit_nom != 'Invitation'
									AND pa.actif = '1'
									AND est_abonnement = '0'
									AND est_cours_particulier = '0'
									ORDER BY date_achat ASC");
									if($checkActive->rowCount() > 0){ // If there are active products that are not an annual sub
										$product = $checkActive->fetch(PDO::FETCH_ASSOC);
									} else { // We check inactive products now.
										$checkPending = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$eleve'
									AND produit_nom != 'Invitation'
									AND pa.actif = '0'
									AND est_abonnement = '0'
									AND est_cours_particulier = '0'
									ORDER BY date_achat ASC");
										if($checkPending->rowCount() > 0){
											$product = $checkPending->fetch(PDO::FETCH_ASSOC);
										}
									}
								}
							}
						}
					}
					echo $product_id = $product["id_produit_adherent"];
					echo "<br>";
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
