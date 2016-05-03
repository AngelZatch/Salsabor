<?php
//require_once "/opt/lampp/htdocs/Salsabor/functions/db_connect.php";
require_once "../db_connect.php";
$db = PDOFactory::getConnection();

/** Daily, this file will try to fix all the errors the system might have done.
- Records with RFID but no user.
Tihs file will be executed at night, before the system operations so errors have a limited impact.
cron line : cron : 30 0 * * * php -f /opt/lampp/htdocs/Salsabor/functions/schedule/system_operations.php
(will be executed daily at 12:30am)
**/

set_time_limit(0);
$limit = date('l',strtotime(date('Y-01-01')));

// Records with a RFID but no user ID
$records = $db->query("SELECT * FROM participations WHERE user_rfid IS NOT NULL AND user_id IS NULL");
while($record = $records->fetch(PDO::FETCH_GROUP)){
	$record_rfid = $record["user_rfid"];
	$participation_id = $record["passage_id"];

	// We find the user with that RFID and we update the record that has missing info.
	try{
		$correct = $db->query("UPDATE participations SET user_id = (SELECT user_id FROM users WHERE user_rfid = '$record_rfid') WHERE passage_id = '$participation_id'");
	} catch(PDOException $e){
		echo $e->getMessage();
	}
}

// Keep RFID up-to-date
$records = $db->query("SELECT * FROM participations");
while($record = $records->fetch(PDO::FETCH_GROUP)){
	$participation_id = $record["passage_id"];
	$user_id = $record["user_id"];
	$correct = $db->query("UPDATE participations SET user_rfid = (SELECT user_rfid FROM users WHERE user_id = '$user_id') WHERE passage_id='$participation_id'");
}

// Records with no room_token but a session_id
$records = $db->query("SELECT * FROM participations WHERE room_token IS NULL OR room_token = '' AND cours_id IS NOT NULL");
while($record = $records->fetch(PDO::FETCH_GROUP)){
	$cours_id = $record["cours_id"];
	$participation_id = $record["passage_id"];
	$correct = $db->query("UPDATE participations SET room_token = (SELECT lecteur_ip FROM cours c JOIN lecteurs_rfid lr ON c.cours_salle = lr.lecteur_lieu WHERE cours_id='$cours_id') WHERE passage_id = '$participation_id'");
}

// Delete all participations with no user at all
$loss = $db->query("DELETE FROM participations WHERE user_id IS NULL AND user_rfid IS NULL");

// Find all duplicates
$duplicates = $db->query("SELECT *, count(*) AS duplicates FROM participations GROUP BY cours_id, user_id HAVING duplicates > 1");
while($duplicate = $duplicates->fetch(PDO::FETCH_ASSOC)){
	$delete = $db->query("DELETE FROM participations WHERE passage_id = '$duplicate[passage_id]'");
}

// Delete "lost" records
$delete = $db->query("DELETE FROM participations WHERE cours_id IS NULL AND produit_adherent_id IS NULL AND passage_date < '$limit'");
?>
