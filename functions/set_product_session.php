<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];
$product_id = $_POST["product_id"];

$load = $db->query("SELECT produit_adherent_id FROM cours_participants WHERE id = '$record_id'")->fetch(PDO::FETCH_ASSOC);

$assign = $db->query("UPDATE cours_participants SET
produit_adherent_id='$product_id' WHERE id='$record_id'");

if($load["produit_adherent_id"] == null){
	echo $product_id;
} else {
	echo $load["produit_adherent_id"];
}
?>
