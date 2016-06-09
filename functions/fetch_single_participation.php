<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$participation_id = $_GET["participation_id"];

$load = $db->query("SELECT *, pa.date_activation AS produit_adherent_activation
							FROM participations pr
							JOIN cours c ON pr.cours_id = c.cours_id
							LEFT JOIN produits_adherents pa ON pr.produit_adherent_id = pa.id_produit_adherent
							LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
							LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
							WHERE pr.passage_id='$participation_id'
							ORDER BY cours_start DESC");

$details = $load->fetch(PDO::FETCH_ASSOC);

$p = array();
$p["id"] = $details["passage_id"];
$p["date"] = $details["cours_start"];
$p["cours_name"] = $details["cours_intitule"];
$p["hour_start"] = $details["cours_start"];
$p["hour_end"] = $details["cours_end"];
$p["product"] = $details["produit_adherent_id"];
$p["achat"] = $details["date_achat"];
$p["product_activation"] = $details["produit_adherent_activation"];
$p["product_expiration"] = max($details["date_expiration"], $details["date_fin_utilisation"], $details["date_prolongee"]);
$p["product_name"] = $details["produit_nom"];
echo json_encode($p);
?>
