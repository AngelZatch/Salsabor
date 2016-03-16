<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

/** Computes the amount of remaining hours on a product based on the sessions taken with it.
This code will also:
- Deactivate the product if the remaining hours are equal or less than 0
	-> Change the date of full consommation
	-> Change the state of the product
	-> Highlight over-consommation sessions (sessions which have been taken using that product when it should have been deactivated - chronological order)**/

$product_id = $_POST["product_id"];

$max_hours = $db->query("SELECT volume_horaire, est_illimite, pa.date_activation AS produit_adherent_activation, volume_horaire, validite_initiale, pa.actif AS produit_adherent_actif, IF(date_prolongee IS NOT NULL, date_prolongee,
						IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
						) AS produit_validity FROM produits_adherents pa
						JOIN produits p ON p.produit_id = pa.id_produit_foreign
						WHERE id_produit_adherent = '$product_id'")->fetch(PDO::FETCH_ASSOC);

$sessions_list = $db->query("SELECT cours_unite, cours_start, cours_end FROM cours_participants cp
							JOIN cours c ON cp.cours_id_foreign = c.cours_id
							WHERE produit_adherent_id = '$product_id'
							ORDER BY cours_start ASC");

$remaining_hours = $max_hours["volume_horaire"];
$computeEnd = false;
$date_fin_utilisation = 'null';

while($session = $sessions_list->fetch(PDO::FETCH_ASSOC)){
	if($max_hours["produit_adherent_actif"] == '0'){
		if($remaining_hours == $max_hours["volume_horaire"]){
			$date_activation = date_create($session["cours_start"])->format("Y-m-d 00:00:00");
			$computeEnd = true;
		}
	}
	$remaining_hours -= floatval($session["cours_unite"]);
	if($max_hours["produit_validity"] == null){
		if($remaining_hours >= 0){
			$date_fin_utilisation = $session["cours_end"];
		}
	}
}

$values = array();
if($remaining_hours <= 0){
	if($max_hours["est_illimite"] == "1"){
		$status = '1';
		$deactivate = $db->query("UPDATE produits_adherents
							SET actif='1', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
		array_push($values, -1 * $remaining_hours); // Position 1 of the array
	} else {
		$status = '2';
		if($max_hours["produit_adherent_actif"] == "2"){
			$deactivate = $db->query("UPDATE produits_adherents
							SET actif='2', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
		} else {
			$deactivate = $db->query("UPDATE produits_adherents
							SET actif='2', date_fin_utilisation='$date_fin_utilisation', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
		}
		array_push($values, $remaining_hours); // Position 1 of the array
	}
} else if($remaining_hours == $max_hours["volume_horaire"]){
	$status = '0';
	array_push($values, $remaining_hours); // Position 1 of the array
	$deactivate = $db->query("UPDATE produits_adherents
							SET actif='0', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
	echo 0;
} else { // If the hours are still in positive.
	array_push($values, $remaining_hours); // Position 1 of the array
	if($computeEnd){ // If the product was not active before and has to be activated.
		$date_fin_utilisation = date_create(computeExpirationDate($db, $date_activation, $max_hours["validite_initiale"]))->format("Y-m-d H:i:s");
		$status = '1';
		$update = $db->query("UPDATE produits_adherents
						SET actif='$status', date_activation = '$date_activation', date_expiration='$date_fin_utilisation', volume_cours = '$remaining_hours'
						WHERE id_produit_adherent = '$product_id'");
	} else { // If the product was already active before.
		if($max_hours["produit_validity"] != '' && date_create($max_hours["produit_validity"])->format("Y-m-d") < date("Y-m-d")){
			$status = '2';
		} else {
			$status = '1';
		}
		$update = $db->query("UPDATE produits_adherents
						SET actif='$status', volume_cours = '$remaining_hours'
						WHERE id_produit_adherent = '$product_id'");
	}
}
array_push($values, $date_fin_utilisation); // Position 0 of the array
array_push($values, $status); // Position 2 of the array
echo json_encode($values);
?>
