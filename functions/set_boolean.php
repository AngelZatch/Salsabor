<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$boolean_name = $_POST["boolean_name"];
$product_id = $_POST["product_id"];
$old_value = $_POST["old_value"];

if($old_value == 1){
	$new_value = 0;
} else {
	$new_value = 1;
}
$update = $db->query("UPDATE produits_adherents SET $boolean_name = $new_value WHERE id_produit_adherent = '$product_id'");
?>
