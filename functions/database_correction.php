<?php
require_once 'db_connect.php';
include "tools.php";
$db = PDOFactory::getConnection();
// The script has to find the correct user_id, session and product for every record of april 11th.

$allRecords = $db->query("SELECT * FROM passages WHERE passage_date > '2016-04-11 00:00:00'");

while($record = $allRecords->fetch(PDO::FETCH_ASSOC)){
	$ip = $record["passage_salle"];
	$tag = $record["passage_eleve"];
	$hour = $record["passage_date"];
	$limit = date("Y-m-d H:i:s", strtotime($record["passage_date"].'+20MINUTES'));
	// Find the session
	$session = $db->query("SELECT cours_intitule, cours_id FROM cours c
								JOIN lecteurs_rfid lr ON c.cours_salle = lr.lecteur_lieu
								WHERE ouvert = '2' AND (cours_start < '$hour' OR cours_start > '$limit') AND cours_end > '$hour' AND lecteur_ip = '$ip'")->fetch(PDO::FETCH_GROUP);
	$cours_name = $session["cours_intitule"];
	$session_id = $session["cours_id"];

	// Find the user
	$user_id = $db->query("SELECT user_id FROM users WHERE user_rfid = '$tag'")->fetch(PDO::FETCH_COLUMN);

	// Find the product
	$today = date_create('now')->format('Y-m-d H:i:s');
	if($session_id != null){ // If we could find a session, then we're gonna look for a product.
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
		if(isset($product)){
			$product_id = $product["id_produit_adherent"];
			$status = "0";
		} else {
			$product = NULL;
			$status = "3";
		}
		$new = $db->query("UPDATE passages SET passage_eleve_id ='$user_id', cours_id = '$session_id', produit_adherent_cible = '$product_id', status = '$status' WHERE passage_id='$record[passage_id]'");
	} else {
		$status = "2";
		$new = $db->query("UPDATE passages SET passage_eleve_id ='$user_id', status = '$status' WHERE passage_id='$record[passage_id]'");
	}
}
?>
