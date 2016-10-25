<?php
session_start();
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$date = new DateTime();
$age = $db->query("SELECT setting_value FROM settings WHERE setting_code = 'archiv_part'")->fetch(PDO::FETCH_COLUMN);
$delta = "P".$age."M";
$date->sub(new DateInterval($delta));
$date = $date->format('Y-m-d H:i:s');

$nombreParticipations = $db->query("SELECT * FROM participations p
								LEFT JOIN sessions s ON p.session_id = s.session_id
								LEFT JOIN rooms r ON s.session_room = r.room_id
								LEFT JOIN locations l ON r.room_location = l.location_id
								WHERE (status = 0 OR status = 3 OR (status = 2 AND (produit_adherent_id IS NULL OR produit_adherent_id = '' OR produit_adherent_id = 0)))
								AND location_id = $_SESSION[location]
								AND passage_date > '$date'")->rowCount();
echo $nombreParticipations;
?>
