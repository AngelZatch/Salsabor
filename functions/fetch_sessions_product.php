<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$product_id = $_GET["product_id"];

$product_details = $db->query("SELECT volume_horaire, est_illimite, pa.date_activation AS produit_adherent_activation,
						IF(date_prolongee IS NOT NULL, date_prolongee,
							IF (date_fin_utilisation IS NOT NULL AND date_fin_utilisation != '0000-00-00 00:00:00', date_fin_utilisation, date_expiration)
							) AS produit_validity FROM produits_adherents pa
						JOIN produits p
							ON pa.id_produit_foreign = p.produit_id
						JOIN transactions t
							ON pa.id_transaction_foreign = t.id_transaction
						WHERE id_produit_adherent = '$product_id'")->fetch(PDO::FETCH_ASSOC);

$participations = $db->query("SELECT * FROM participations pr
							JOIN cours c ON pr.session_id = c.session_id
							WHERE produit_adherent_id = '$product_id'
							AND (status = 0 OR status = 2)
							ORDER BY session_start ASC");

$remaining_hours = $product_details["volume_horaire"];
$date_fin_utilisation = $product_details["produit_validity"];
$participations_list = array();

while($participation = $participations->fetch(PDO::FETCH_ASSOC)){
	$p = array();
	$p["id"] = $participation["passage_id"];
	$p["title"] = $participation["session_name"];
	$p["start"] = $participation["session_start"];
	$p["end"] = $participation["session_end"];
	$p["duration"] = $participation["cours_unite"];

	if($p["start"] > $product_details["produit_validity"] || $p["start"] < $product_details["produit_adherent_activation"] || ($remaining_hours <= 0 && $product_details["est_illimite"] != "1")){
		$p["valid"] = "2"; // The session happened after the product expired or before it activated or the product didn't have any hours left.
		if($p["start"] > $product_details["produit_validity"]){
			$p["reason"] = "Start (".$p["start"].") is after the expiration date (".$product_details["produit_validity"].")";
		}
		if($p["start"] < $product_details["produit_adherent_activation"]){
			$p["reason"] = "Start is before the activation date (".$product_details["produit_adherent_activation"].")";
		}
	} else {
		$p["valid"] = "1";
	}
	$p["status"] = $participation["status"];
	array_push($participations_list, $p);

	$remaining_hours -= floatval($participation["cours_unite"]);
}

echo json_encode($participations_list);
?>
