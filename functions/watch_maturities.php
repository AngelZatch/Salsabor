<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$nombreEcheancesDues = $db->query("SELECT * FROM produits_echeances WHERE echeance_effectuee=2")->rowCount();
echo $nombreEcheancesDues;
?>