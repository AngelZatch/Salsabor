<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

$name = $_POST["name"];
$session_id = $_POST["session_id"];

$stmt = $db->prepare("SELECT * FROM (
	SELECT user_id, user_rfid, CONCAT(user_prenom, ' ', user_nom) as fullname FROM users) base
	WHERE fullname = ?");
$stmt->bindParam(1, $name, PDO::PARAM_STR);
$stmt->execute();
$user_details = $stmt->fetch(PDO::FETCH_ASSOC);
$user_id = $user_details["user_id"];
$user_rfid = $user_details["user_rfid"];

$session_details = $db->query("SELECT cours_intitule, reader_token FROM cours c
							JOIN rooms r ON c.cours_salle = r.room_id
							LEFT JOIN readers re ON r.room_reader = re.reader_id
							WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_ASSOC);
$cours_name = $session_details["cours_intitule"];
$reader_token = $session_details["reader_token"];

addParticipation($db, $cours_name, $session_id, $user_id, $reader_token, $user_rfid);

?>
