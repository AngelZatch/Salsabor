<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

/** Marks a maturity as banked **/

$id = $_POST["maturity_id"];

if(isset($_POST["date"])){
	$date_reception = $_POST["date"];
	$update = $db->query("UPDATE produits_echeances SET statut_banque = '1', date_encaissement = '$date_reception' WHERE produits_echeances_id='$id'");
} else {
	$update = $db->query("UPDATE produits_echeances SET statut_banque = '0', date_encaissement = NULL WHERE produits_echeances_id = '$id'");
}
?>
