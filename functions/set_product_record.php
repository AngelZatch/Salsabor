<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];
$product_id = $_POST["product_id"];

$load = $db->query("SELECT produit_nom, pa.actif AS produit_adherent_actif FROM produits p
					JOIN produits_adherents pa ON pa.id_produit_foreign = p.produit_id WHERE id_produit_adherent = '$product_id'")->fetch(PDO::FETCH_ASSOC);
if($load["produit_adherent_actif"] == '2'){
	$status = '3';
} else {
	$status = '0';
}
$assign = $db->query("UPDATE passages SET produit_adherent_cible = '$product_id', status = '$status' WHERE passage_id = '$record_id'");

$r = array();
$r["status"] = $status;
$r["product_name"] = $load["produit_nom"];

echo json_encode($r);
?>
