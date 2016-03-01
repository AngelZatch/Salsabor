<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

/** Marks a maturity as paiement pending **/

$id = $_POST["maturity_id"];
$date_reception = date_create("now")->format("Y-m-d");

$maturity = $db->query("SELECT date_echeance FROM produits_echeances WHERE produits_echeances_id=$id")->fetch(PDO::FETCH_ASSOC);
if($maturity["date_echeance"] < $date_reception){
	$newState = 2;
} else {
	$newState = 0;
}

$update = $db->query("UPDATE produits_echeances SET echeance_effectuee = '$newState', date_paiement = NULL WHERE produits_echeances_id='$id'");

echo $newState;
?>
