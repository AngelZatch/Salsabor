<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

/** Extends the validity of a product **/

$product_id = $_POST["product_id"];
$end_date = $_POST["end_date"];
if($end_date){
	$end_date = $_POST["end_date"];
	$activate = $db->query("UPDATE produits_adherents
						SET date_prolongee = '$end_date'
						WHERE id_produit_adherent = '$product_id'");
} else {
	$activate = $db->query("UPDATE produits_adherents
						SET date_prolongee = null
						WHERE id_produit_adherent = '$product_id'");
}
?>
