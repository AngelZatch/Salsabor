<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$nombreParticipants = $db->query("SELECT * FROM cours_participants WHERE produit_adherent_id='NULL'")->rowCount();
echo $nombreParticipants;
?>