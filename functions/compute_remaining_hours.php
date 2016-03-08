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

$max_hours = $db->query("SELECT volume_horaire FROM produits_adherents pa
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
	$today = date_create("now")->format("Y-m-d");
	$deactivate = $db->query("UPDATE produits_adherents
							SET actif='2', date_fin_utilisation='$date_fin_utilisation'
							WHERE id_produit_adherent = '$product_id'");
	echo $date_fin_utilisation;
} else {
	$update = $db->query("UPDATE produits_adherents
						SET volume_cours = '$remaining_hours'
						WHERE id_produit_adherent = '$product_id'");
	echo $remaining_hours;
}
?>
