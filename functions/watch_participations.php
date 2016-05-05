<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$nombreParticipations = $db->query("SELECT * FROM participations WHERE status = 0 OR status = 3 OR (status = 2 AND (produit_adherent_id IS NULL OR produit_adherent_id = '' OR produit_adherent_id = 0))")->rowCount();
echo $nombreParticipations;
?>
