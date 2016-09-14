<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

$name = $_POST["name"];
$session_id = $_POST["session_id"];
$values = array();

$stmt = $db->prepare("SELECT * FROM (
	SELECT user_id, user_rfid, CONCAT(user_prenom, ' ', user_nom) as fullname FROM users) base
	WHERE fullname = ?");
$stmt->bindParam(1, $name, PDO::PARAM_STR);
$stmt->execute();
$user_details = $stmt->fetch(PDO::FETCH_ASSOC);

$values["user_id"] = $user_details["user_id"];
$values["user_rfid"] = $user_details["user_rfid"];

$reader_token = $db->query("SELECT reader_token FROM sessions s
							JOIN rooms r ON s.session_room = r.room_id
							LEFT JOIN readers re ON r.room_reader = re.reader_id
							WHERE session_id = '$session_id'")->fetch(PDO::FETCH_COLUMN);

$values["passage_date"] = date("d/m/Y H:i:s");
$values["room_token"] = $reader_token;
$values["session_id"] = $session_id;

addParticipationBeta($db, $values);
?>
