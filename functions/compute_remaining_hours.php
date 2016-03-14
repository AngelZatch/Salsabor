<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

/** Computes the amount of remaining hours on a product based on the sessions taken with it.
This code will also:
- Deactivate the product if the remaining hours are equal or less than 0
	-> Change the date of full consommation
	-> Change the state of the product
	-> Highlight over-consommation sessions (sessions which have been taken using that product when it should have been deactivated - chronological order)**/

$product_id = $_POST["product_id"];

$max_hours = $db->query("SELECT volume_horaire, est_illimite, pa.date_activation AS produit_adherent_activation, pa.actif AS produit_adherent_actif FROM produits_adherents pa
						JOIN produits p ON p.produit_id = pa.id_produit_foreign
						WHERE id_produit_adherent = '$product_id'")->fetch(PDO::FETCH_ASSOC);

$sessions_list = $db->query("SELECT cours_unite, cours_end FROM cours_participants cp
							JOIN cours c ON cp.cours_id_foreign = c.cours_id
							WHERE produit_adherent_id = '$product_id'");

$remaining_hours = $max_hours["volume_horaire"];

while($session = $sessions_list->fetch(PDO::FETCH_ASSOC)){
	$remaining_hours -= floatval($session["cours_unite"]);
	$date_fin_utilisation = $session["cours_end"];
}

if($remaining_hours <= 0){
	/*if($max_hours["produit_adherent_actif"] != "2"){
		$today = date_create("now")->format("Y-m-d");
	}*/
	$values = array();
	array_push($values, $date_fin_utilisation);
	if($max_hours["est_illimite"] == "1"){
		$deactivate = $db->query("UPDATE produits_adherents
							SET actif='1', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
		array_push($values, -1 * $remaining_hours);
	} else {
		if($max_hours["produit_adherent_actif"] == "2"){
			$deactivate = $db->query("UPDATE produits_adherents
							SET actif='2', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
		} else {
			$deactivate = $db->query("UPDATE produits_adherents
							SET actif='2', date_fin_utilisation='$date_fin_utilisation', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
		}
		array_push($values, $remaining_hours);
	}
	echo json_encode($values);
} else if($remaining_hours == $max_hours["volume_horaire"]){
	$deactivate = $db->query("UPDATE produits_adherents
							SET actif='0', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
	echo 0;
} else {
	if($max_hours["produit_adherent_activation"] == "0000-00-00 00:00:00"){
		$date_activation = date("Y-m-d H:i:s");
		$update = $db->query("UPDATE produits_adherents
						SET actif='1', date_activation = '$date_activation', volume_cours = '$remaining_hours'
						WHERE id_produit_adherent = '$product_id'");
	} else {
		$update = $db->query("UPDATE produits_adherents
						SET actif='1', volume_cours = '$remaining_hours'
						WHERE id_produit_adherent = '$product_id'");
	}
	echo $remaining_hours;
}
?>
