<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$session_id = $_POST["session_id"];
$user_id = $_POST["user_id"];
$record_id = $_POST["record_id"];
$product_id = $_POST["product_id"];

$cours_name = $db->query("SELECT cours_intitule FROM cours WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_COLUMN);

/** This code has to find the appropriate product to use for every single type of record ever. Once it has found the appropriate record, it will return his number and then the JS will call "Compute" with it. **/

if($product_id == ""){ // If the product has not been manually set
	// CASE WHERE THIS IS NOT A PRIVATE SESSION
	if(preg_match("/jazz/i", $cours_name, $matches) || preg_match("/pilates/i", $cours_name, $matches) || preg_match("/particulier/i", $cours_name, $matches)){ // Search for specific Jazz, Pilates or private sessions
		/*echo $matches[0];*/
		$checkSpecific = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom LIKE '%$matches[0]%'
									AND pa.actif != '2'
									ORDER BY date_achat ASC");
		if($checkSpecific->rowCount() > 0){
			$product = $checkSpecific->fetch(PDO::FETCH_ASSOC);
		}
	} else { // First, we search for any freebies
		$checkInvitation = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom = 'Invitation'
									AND pa.actif = '0'
									ORDER BY date_achat ASC");
		if($checkInvitation->rowCount() > 0){ // If there are freebies still available, we take the first one.
			$product = $checkInvitation->fetch(PDO::FETCH_ASSOC);
		} else { // If no freebies, we look for every currently active products.
			$checkActive = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
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
									WHERE id_user_foreign='$user_id'
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
if(isset($product)){
	$product_id = $product["id_produit_adherent"];
} else {
	$product = NULL;
}

// Confirming the record as a fleshed out participation
$new = $db->prepare("INSERT INTO cours_participants(cours_id_foreign, eleve_id_foreign, produit_adherent_id)
						VALUES(:cours, :eleve, :produit)");
$new->bindParam(':cours', $session_id);
$new->bindParam(':eleve', $user_id);
$new->bindParam(':produit', $product_id);
$new->execute();

// Update the record as handled with the correct session and status
$update = $db->query("UPDATE passages SET cours_id='$session_id', status=2 WHERE passage_id='$record_id'");

echo $product_id;
?>
