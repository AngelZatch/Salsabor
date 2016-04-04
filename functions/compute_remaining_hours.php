<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

/**
This code will:
- Compute the amount of remaining hours on a product based on the sessions taken with it.
- Deactivate the product if the remaining hours are equal or less than 0
- Activate the product if it has recieved records while it was still pending
- Compute the activation and expiration date everytime to act as a failsafe is the auto-activation script fails (it does)

Yes. This code does everything to ensure the information can be tracked and stay as accurate as possible.
**/

$product_id = $_POST["product_id"];

$product_details = $db->query("SELECT volume_horaire, est_illimite, est_abonnement, pa.date_activation AS produit_adherent_activation, validite_initiale, pa.actif AS produit_adherent_actif, date_achat, date_expiration, date_prolongee, date_fin_utilisation,
						IF(date_prolongee IS NOT NULL, date_prolongee,
							IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
							) AS produit_validity FROM produits_adherents pa
						JOIN produits p
							ON pa.id_produit_foreign = p.produit_id
						JOIN transactions t
							ON pa.id_transaction_foreign = t.id_transaction
						WHERE id_produit_adherent = '$product_id'")->fetch(PDO::FETCH_ASSOC);

$remaining_hours = $product_details["volume_horaire"];
$v = array();
$computeEnd = false;
$status = $product_details["produit_adherent_actif"];
$date_activation = $product_details["produit_adherent_activation"];

if($product_details["est_abonnement"] == '0'){
	$sessions = $db->query("SELECT cours_unite, cours_start, cours_end FROM cours_participants cp
							JOIN cours c ON cp.cours_id_foreign = c.cours_id
							WHERE produit_adherent_id = '$product_id'
							ORDER BY cours_start ASC");
	foreach($sessions as $session){
		if(!$computeEnd){
			// If the product's current hours are max OR there's no set activation date, we compute the activation date. This will only occur one time to ensure the date of activation is always accurate.
			$date_activation = date_create($session["cours_start"])->format("Y-m-d H:i:s");
			if($date_activation != null && $date_activation != "0000-00-00 00:00:00"){
				$computeEnd = true;
			}
		}
	}
	if($computeEnd){ // We compute the date of expiration
		$date_expiration = date_create(computeExpirationDate($db, $date_activation, $product_details["validite_initiale"]))->format("Y-m-d H:i:s");
		$farthest = ($date_expiration>$product_details["date_prolongee"])?$date_expiration:$product_details["date_prolongee"];
	}
	$sessions->execute();
	foreach($sessions as $session){
		if($farthest >= $session["cours_end"] && $remaining_hours >= 0){
			// If there's no expiration date set for the product or (it is ANTERIOR to the date of the session BUT there's still hours on the product) or (it is POSTERIOR to the date of the session BUT there's no more hours on the product), the expiration date is set to the last session that happened.
			$date_fin_utilisation = $session["cours_end"];
		}
		$remaining_hours -= floatval($session["cours_unite"]);
	}
	// We update the number of hours
	if($remaining_hours <= 0){ // If the number of remaining hours is negative
		if($product_details["est_illimite"] == "1"){
			if($remaining_hours == '0'){
				$status = '0';
				$date_activation = NULL;
				$date_expiration = NULL;
				$deactivate = $db->query("UPDATE produits_adherents
							SET date_activation = NULL, actif='$status', volume_cours = '$remaining_hours', date_expiration = NULL
							WHERE id_produit_adherent = '$product_id'");
			} else if($date_expiration < date("Y-m-d")){
				if($product_details["date_prolongee"] != null && $product_details["date_prolongee"] > date("Y-m-d H:i:s")){
					$status = '1';
				} else {
					$status = '2';
				}
				$deactivate = $db->query("UPDATE produits_adherents
							SET date_activation = '$date_activation', actif='$status', volume_cours = '$remaining_hours', date_expiration = '$date_expiration'
							WHERE id_produit_adherent = '$product_id'");
			} else {
				$status = '1';
				$deactivate = $db->query("UPDATE produits_adherents
							SET date_activation = '$date_activation', actif='$status', volume_cours = '$remaining_hours', date_expiration = '$date_expiration'
							WHERE id_produit_adherent = '$product_id'");
			}
			$v["hours"] = -1 * $remaining_hours;
		} else {
			$status = '2';
			if($date_fin_utilisation < $product_details["produit_validity"]){
				$deactivate = $db->query("UPDATE produits_adherents
							SET date_activation = '$date_activation', actif='$status', date_fin_utilisation='$date_fin_utilisation', date_expiration = '$date_expiration', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
			} else {
				$deactivate = $db->query("UPDATE produits_adherents
							SET date_activation = '$date_activation', actif='$status', volume_cours = '$remaining_hours', date_expiration = '$date_expiration'
							WHERE id_produit_adherent = '$product_id'");
			}
			$v["hours"] = 1 * $remaining_hours;
		}
	} else if($remaining_hours == $product_details["volume_horaire"]){
		$status = '0';
		$v["hours"] = 1 * $remaining_hours;
		$deactivate = $db->query("UPDATE produits_adherents
							SET date_activation = NULL, date_expiration = NULL, date_fin_utilisation = NULL, actif='$status', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
	} else { // If the hours are still in positive.
		$v["hours"] = 1 * $remaining_hours;
		if($computeEnd){ // If the expiration date has to be computed.
			if($date_expiration < date("Y-m-d")){ // If the computed expiration date is anterior to today, then the product should be expired.
				if($product_details["date_prolongee"] != null && $product_details["date_prolongee"] > date("Y-m-d H:i:s")){
					$status = '1';
				} else {
					$status = '2';
				}
			} else {
				$status = '1';
			}
			// We remove the date of fully use, since hours remain.
			$update = $db->query("UPDATE produits_adherents
						SET actif='$status', date_activation = '$date_activation', date_expiration='$date_expiration', date_fin_utilisation = NULL, volume_cours = '$remaining_hours'
						WHERE id_produit_adherent = '$product_id'");
		} else { // It the expiration date doesn't have to be computed
			if($product_details["produit_validity"] != '' && date_create($product_details["produit_validity"])->format("Y-m-d H:i:s") < date("Y-m-d H:i:s")){ // If there's an expiration date set AND it's anterior to today
				$status = '2';
			} else {
				$status = '1';
			}
			$update = $db->query("UPDATE produits_adherents SET date_activation = '$date_activation', actif='$status', volume_cours = '$remaining_hours', date_fin_utilisation = NULL WHERE id_produit_adherent = '$product_id'");
		}
	}
} else {
	$v["hours"] = 0;
}

$v["activation"] = $date_activation;
if($product_details["date_prolongee"] != null && $product_details["date_prolongee"] != "0000-00-00 00:00:00"){
	$v["expiration"] = $product_details["date_prolongee"];
} else {
	if(isset($date_expiration)){
		$v["expiration"] = $date_expiration;
	} else {
		$v["expiration"] = null;
	}
}
if(isset($date_fin_utilisation)){
	$v["usage"] = $date_fin_utilisation;
} else {
	$v["usage"] = null;
}
$v["status"] = $status;
$v["limit"] = $product_details["est_illimite"];
$v["compute"] = $computeEnd;
echo json_encode($v);
?>
