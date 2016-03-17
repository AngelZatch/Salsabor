<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$product_id = $_GET["product_id"];

$product_details = $db->query("SELECT volume_horaire, est_illimite, pa.date_activation AS produit_adherent_activation,
						IF(date_prolongee IS NOT NULL, date_prolongee,
							IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
							) AS produit_validity FROM produits_adherents pa
						JOIN produits p
							ON pa.id_produit_foreign = p.produit_id
						JOIN transactions t
							ON pa.id_transaction_foreign = t.id_transaction
						WHERE id_produit_adherent = '$product_id'")->fetch(PDO::FETCH_ASSOC);

$sessions_list = $db->query("SELECT * FROM cours_participants cp
							JOIN cours c ON cp.cours_id_foreign = c.cours_id
							WHERE produit_adherent_id = '$product_id'
							ORDER BY cours_start ASC");

$remaining_hours = $product_details["volume_horaire"];
$date_fin_utilisation = $product_details["produit_validity"];
$sessionsList = array();

while($session = $sessions_list->fetch(PDO::FETCH_ASSOC)){
	$s = array();
	$s["id"] = $session["id"];
	$s["title"] = $session["cours_intitule"];
	$s["start"] = $session["cours_start"];
	$s["end"] = $session["cours_end"];
	$s["duration"] = $session["cours_unite"];

	if(date_create($s["start"])->format("Y-m-d") > date_create($product_details["produit_validity"])->format("Y-m-d") || date_create($s["start"])->format("Y-m-d") < date_create($product_details["produit_adherent_activation"])->format("Y-m-d") || ($remaining_hours <= 0 && $product_details["est_illimite"] != "1")){
		$s["valid"] = "2"; // The session happened after the product expired or before it activated or the product didn't have any hours left.
	} else {
		$s["valid"] = "1";
	}
	array_push($sessionsList, $s);

	$remaining_hours -= floatval($session["cours_unite"]);
}

echo json_encode($sessionsList);
?>
