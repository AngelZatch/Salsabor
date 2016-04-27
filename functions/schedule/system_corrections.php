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
?>
