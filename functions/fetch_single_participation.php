<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$participation_id = $_GET["participation_id"];

$load = $db->query("SELECT *, pa.date_activation AS produit_adherent_activation,
							IF(date_prolongee IS NOT NULL, date_prolongee,
								IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
								) AS produit_validity
							FROM cours_participants cp
							JOIN cours c ON cp.cours_id_foreign=c.cours_id
							LEFT JOIN produits_adherents pa ON cp.produit_adherent_id=pa.id_produit_adherent
							LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
							LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
							WHERE cp.id='$participation_id'
							ORDER BY cours_start DESC");

$details = $load->fetch(PDO::FETCH_ASSOC);

$p = array();
$p["id"] = $details["id"];
$p["date"] = $details["cours_start"];
$p["cours_name"] = $details["cours_intitule"];
$p["hour_start"] = $details["cours_start"];
$p["hour_end"] = $details["cours_end"];
$p["product"] = $details["produit_adherent_id"];
$p["achat"] = $details["date_achat"];
$p["product_activation"] = $details["produit_adherent_activation"];
$p["product_validity"] = $details["produit_validity"];
$p["product_name"] = $details["produit_nom"];
echo json_encode($p);
?>
