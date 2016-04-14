<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$update = $db->query("UPDATE produits_echeances SET lock_montant = 1");
$update = $db->query("UPDATE produits_adherents SET lock_status = 1, lock_dates = 1");
?>
