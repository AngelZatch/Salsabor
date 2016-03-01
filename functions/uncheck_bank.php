<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

/** Marks a maturity as bank pending **/

$id = $_POST["maturity_id"];
$date_reception = date_create("now")->format("Y-m-d");

$update = $db->query("UPDATE produits_echeances SET statut_banque = '0', date_encaissement = NULL WHERE produits_echeances_id='$id'");
?>
