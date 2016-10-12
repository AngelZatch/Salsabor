<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$date = new DateTime();
$age = $db->query("SELECT setting_value FROM settings WHERE setting_code = 'archiv_part'")->fetch(PDO::FETCH_COLUMN);
$delta = "P".$age."M";
$date->sub(new DateInterval($delta));
$date = $date->format('Y-m-d H:i:s');

$nombreParticipations = $db->query("SELECT * FROM participations WHERE (status = 0 OR status = 3 OR (status = 2 AND (produit_adherent_id IS NULL OR produit_adherent_id = '' OR produit_adherent_id = 0))) AND passage_date > '$date'")->rowCount();
echo $nombreParticipations;
?>
