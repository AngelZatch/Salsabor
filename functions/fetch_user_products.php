<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];

$record = $db->query("SELECT passage_date FROM passages
					WHERE passage_id = '$record_id'")->fetch(PDO::FETCH_ASSOC);

$load = $db->query("SELECT id_produit_adherent, pa.actif AS produit_adherent_actif, pa.date_activation AS produit_adherent_activation, produit_nom, date_achat FROM produits_adherents pa
					JOIN produits p
						ON pa.id_produit_foreign = p.produit_id
					JOIN transactions t
						ON pa.id_transaction_foreign = t.id_transaction
					WHERE id_user_foreign =
						(SELECT eleve_id_foreign
							FROM cours_participants
							WHERE id='$record_id')
						AND (pa.actif != '2' OR (pa.actif = '2' AND est_illimite = '1' AND (pa.date_activation < '$record[passage_date]' AND date_expiration > '$record[passage_date]')))
						AND est_abonnement != '1'");

$productList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$p = array();
	$p["id"] = $details["id_produit_adherent"];
	$p["status"] = $details["produit_adherent_actif"];
	$p["title"] = $details["produit_nom"];
	$p["transaction_achat"] = $details["date_achat"];
	array_push($productList, $p);
}
echo json_encode($productList);
?>
