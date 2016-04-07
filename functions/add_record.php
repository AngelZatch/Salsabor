<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

$name = $_POST["name"];
$session_id = $_POST["session_id"];

$user_details = $db->query("SELECT * FROM (
	SELECT user_id, user_rfid, CONCAT(user_prenom, ' ', user_nom) as fullname FROM users) base
	WHERE fullname = '$name'")->fetch(PDO::FETCH_ASSOC);
$user_id = $user_details["user_id"];
$user_rfid = $user_details["user_rfid"];

$session_details = $db->query("SELECT cours_intitule, lecteur_ip
							FROM cours c
							JOIN lecteurs_rfid lr ON c.cours_salle = lr.lecteur_lieu
							WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_ASSOC);
$cours_name = $session_details["cours_intitule"];
$ip = $session_details["lecteur_ip"];

addRecord($db, $cours_name, $session_id, $user_id, $ip, $user_rfid);

?>
