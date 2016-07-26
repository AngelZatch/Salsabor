<?php
require_once "../Salsabor/functions/db_connect.php";
include "../Salsabor/functions/tools.php";
$db = PDOFactory::getConnection();

$data = explode('*', $_GET["carte"]);
$tag_rfid = $data[0];
$reader_token = $data[1];

//prepareParticipation($db, $tag_rfid, $reader_token);
prepareParticipationBeta($db, $tag_rfid, $reader_token);

function prepareParticipation($db, $user_tag, $reader_token){
	$today = date("Y-m-d H:i:s");
	//$limit = date("Y-m-d H:i:s", strtotime($today.'+20MINUTES'));
	if($reader_token == "192.168.0.3"){
		$status = "1";
		$new = $db->query("INSERT INTO participations(user_rfid, room_token, passage_date, status)
					VALUES('$user_tag', '$reader_token', '$today', '$status')");
		echo "$";
	} else {
		// If the tag is not for associating, we search a product that could be used for this session.
		// First, we get the name of the session and the ID of the user.
		// For the session, we have to find it based on the time of the record and the position.
		$session = $db->query("SELECT session_name, session_id FROM cours c
								JOIN rooms r ON c.session_room = r.room_id
								JOIN readers re ON r.room_reader = re.reader_id
								WHERE ouvert = '1' AND reader_token = '$reader_token'")->fetch(PDO::FETCH_GROUP);
		$cours_name = $session["session_name"];
		$session_id = $session["session_id"];
		$user_details = $db->query("SELECT user_id, mail FROM users WHERE user_rfid = '$user_tag'")->fetch(PDO::FETCH_ASSOC);

		// Ok, we got everything, let's look for potential duplicates
		$duplicates = $db->query("SELECT * FROM participations WHERE user_rfid = '$user_tag' AND session_id = '$session_id'")->rowCount();

		if($duplicates > 0){
			echo "$";
		} else {
			addParticipation($db, $cours_name, $session_id, $user_details["user_id"], $reader_token, $user_tag);
		}
	}
}

function prepareParticipationBeta($db, $user_tag, $reader_token){
	$today = date("Y-m-d H:i:s");
	if($reader_token == "192.168.0.3"){
		$status = "1";
		$new = $db->query("INSERT INTO participations(user_rfid, room_token, passage_date, status)
					VALUES('$user_tag', '$reader_token', '$today', '$status')");
		echo "$";
	} else {
		// If the tag is not for associating, we search a product that could be used for this session.
		// First, we get the name of the session and the ID of the user.
		// For the session, we have to find it based on the time of the record and the position.
		$session_id = $db->query("SELECT session_id FROM cours c
								JOIN rooms r ON c.session_room = r.room_id
								WHERE ouvert = '1' AND room_reader = '$reader_token'")->fetch(PDO::FETCH_COLUMN);
		$user_id = $db->query("SELECT user_id FROM users WHERE user_rfid = '$user_tag'")->fetch(PDO::FETCH_COLUMN);

		addParticipationBeta($db, $today, $session_id, $user_details["user_id"], $reader_token, $user_tag);
	}
}
