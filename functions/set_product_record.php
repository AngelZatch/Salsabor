<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];
$product_id = $_POST["product_id"];

$assign = $db->query("UPDATE passages SET produit_adherent_cible = '$product_id' WHERE passage_id = '$record_id'");

$load = $db->query("SELECT produit_nom FROM produits p
					JOIN produits_adherents pa ON pa.id_produit_foreign = p.produit_id WHERE id_produit_adherent = '$product_id'")->fetch(PDO::FETCH_COLUMN);

echo $load;
?>
