<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$transaction = $_POST["purchase_id"];

$load = $db->query("SELECT *, pa.actif AS produit_adherent_actif, pa.date_activation AS produit_adherent_activation, user_prenom, user_nom,
					IF(date_prolongee IS NOT NULL, date_prolongee,
						IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
						) AS produit_validity
					FROM produits_adherents pa
					JOIN produits p ON pa.id_produit_foreign = p.produit_id
					JOIN users u ON pa.id_user_foreign = u.user_id
						WHERE id_transaction_foreign = '$transaction'
						ORDER BY prix_achat DESC");

$productList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$p = array();
	$p["id"] = $details["id_produit_adherent"];
	$p["recipient"] = $details["id_user_foreign"];
	$p["product"] = $details["produit_nom"];
	$p["activation"] = $details["produit_adherent_activation"];
	$p["validity"] = $details["produit_validity"];
	$p["hours"] = $details["volume_horaire"];
	$p["remaining_hours"] = $details["volume_cours"];
	$p["price"] = $details["prix_achat"];
	$p["illimited"] = $details["est_illimite"];
	$p["subscription"] = $details["est_abonnement"];
	$p["user"] = $details["user_prenom"]." ".$details["user_nom"];
	if($details["est_illimite"] == 1 || $details["est_cours_particulier"] == 1 || $details["est_abonnement"]){
		$p["flag_hours"] = 0;
	} else {
		$p["flag_hours"] = 1;
	}
	$p["status"] = $details["produit_adherent_actif"];
	array_push($productList, $p);
}
echo json_encode($productList);
?>
