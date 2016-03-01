<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$transaction = $_POST["purchase_id"];

$load = $db->query("SELECT *, pa.actif AS produit_adherent_actif, pa.date_activation AS produit_adherent_activation FROM produits_adherents pa
					JOIN produits p ON pa.id_produit_foreign = p.produit_id
						WHERE id_transaction_foreign = '$transaction'
						ORDER BY prix_achat DESC");

$productList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$p = array();
	$p["id"] = $details["id_produit_adherent"];
	$p["recipient"] = $details["id_user_foreign"];
	$p["product"] = $details["produit_nom"];
	$p["activation"] = $details["produit_adherent_activation"];
	$p["validity"] = $details["date_expiration"];
	$p["used"] = $details["date_fin_utilisation"];
	$p["hours"] = $details["volume_horaire"];
	$p["remaining_hours"] = $details["volume_cours"];
	$p["price"] = $details["prix_achat"];
	if($details["est_illimite"] == 1 || $details["est_cours_particulier"] == 1 || $details["est_abonnement"]){
		$p["flag_hours"] = 0;
	} else {
		$p["flag_hours"] = 1;
	}
	$p["status"] = $details["produit_adherent_actif"];
	$p["extra_life"] = $details["date_prolongee"];
	array_push($productList, $p);
}
echo json_encode($productList);
?>
