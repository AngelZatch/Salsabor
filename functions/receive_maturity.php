<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

/** Marks a maturity as paiement received **/

$id = $_POST["maturity_id"];
$date_reception = $_POST["date"];

if($date_reception != "null"){
	$update = $db->query("UPDATE produits_echeances SET echeance_effectuee = '1', date_paiement = '$date_reception' WHERE produits_echeances_id='$id'");
} else {
	$update = $db->query("UPDATE produits_echeances SET echeance_effectuee = '0', date_paiement = NULL WHERE produits_echeances_id = '$id'");
}
?>
