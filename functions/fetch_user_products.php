<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];

$record = $db->query("SELECT passage_date FROM passages
					WHERE passage_id = '$record_id'")->fetch(PDO::FETCH_ASSOC);

$passage_date = date_create($record["passage_date"])->format("Y-m-d");

$load = $db->query("SELECT id_produit_adherent, pa.actif AS produit_adherent_actif, pa.date_activation AS produit_adherent_activation, produit_nom, date_achat, est_illimite, volume_cours,
					IF(date_prolongee IS NOT NULL, date_prolongee,
						IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
						) AS produit_validity FROM produits_adherents pa
					JOIN produits p
						ON pa.id_produit_foreign = p.produit_id
					LEFT JOIN transactions t
						ON pa.id_transaction_foreign = t.id_transaction
					WHERE id_user_foreign =
						(SELECT eleve_id_foreign
							FROM cours_participants
							WHERE id='$record_id')
						AND (pa.actif != '2' OR (pa.actif = '2' AND pa.date_activation < '$passage_date'))
						AND est_abonnement != '1'");

$productList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$p = array();
	$p["id"] = $details["id_produit_adherent"];
	$p["status"] = $details["produit_adherent_actif"];
	$p["title"] = $details["produit_nom"];
	$p["transaction_achat"] = $details["date_achat"];
	$p["start"] = $details["produit_adherent_activation"];
	$p["validity"] = $details["produit_validity"];
	$p["hours"] = $details["volume_cours"];
	$p["unlimited"] = $details["est_illimite"];
	array_push($productList, $p);
}
echo json_encode($productList);
?>
