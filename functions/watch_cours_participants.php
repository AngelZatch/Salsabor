<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$nombreParticipants = $db->query("SELECT * FROM participations WHERE produit_adherent_id IS NULL OR produit_adherent_id = '' OR produit_adherent_id = 0")->rowCount();
echo $nombreParticipants;
?>
