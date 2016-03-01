<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$transaction = $_POST["purchase_id"];

$load = $db->query("SELECT * FROM produits_echeances pe
						WHERE reference_achat = '$transaction'
						ORDER BY date_echeance DESC");

$maturitys_list = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$m = array();
	$m["id"] = $details["produits_echeances_id"];
	$m["payer"] = $details["payeur_echeance"];
	$m["date"] = $details["date_echeance"];
	$m["price"] = $details["montant"];
	$m["method"] = $details["methode_paiement"];
	$m["reception_status"] = $details["echeance_effectuee"];
	$m["date_reception"] = $details["date_paiement"];
	$m["bank_status"] = $details["statut_banque"];
	$m["date_bank"] = $details["date_encaissement"];
	array_push($maturitys_list, $m);
}
echo json_encode($maturitys_list);
?>
