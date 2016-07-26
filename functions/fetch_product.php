<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$product_id = $_GET["product_id"];

$load = $db->query("SELECT *, pa.actif AS produit_adherent_actif, pa.date_activation AS produit_adherent_activation, date_achat,
					IF(date_prolongee IS NOT NULL, date_prolongee,
						IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
						) AS produit_validity
					FROM produits_adherents pa
					JOIN produits p ON pa.id_produit_foreign = p.product_id
					LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
					JOIN users u ON pa.id_user_foreign = u.user_id
						WHERE id_produit_adherent = '$product_id'");

$details = $load->fetch(PDO::FETCH_ASSOC);

$p = array();
$p["id"] = $details["id_produit_adherent"];
$p["recipient"] = $details["id_user_foreign"];
$p["transaction"] = $details["id_transaction_foreign"];
$p["transaction_date"] = $details["date_achat"];
$p["user"] = $details["user_prenom"]." ".$details["user_nom"];
$p["product"] = $details["product_name"];
$p["activation"] = $details["produit_adherent_activation"];
$p["validity"] = $details["produit_validity"];
$p["used"] = $details["date_fin_utilisation"];
$p["hours"] = $details["product_size"];
$p["remaining_hours"] = $details["volume_cours"];
$p["price"] = $details["prix_achat"];
$p["illimited"] = $details["est_illimite"];
$p["subscription"] = $details["est_abonnement"];
$p["extended"] = $details["date_prolongee"];
if($details["est_illimite"] == 1 || $details["est_cours_particulier"] == 1 || $details["est_abonnement"]){
	$p["flag_hours"] = 0;
} else {
	$p["flag_hours"] = 1;
}
$p["status"] = $details["produit_adherent_actif"];
$p["lock_status"] = $details["lock_status"];
$p["lock_dates"] = $details["lock_dates"];
echo json_encode($p);
?>
