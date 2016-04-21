<?php
require_once "../db_connect.php";
$db = PDOFactory::getConnection();

/* This file has to open sessions
cron : * / 5 10-23 * * * php -f /opt/lampp/htdocs/Salsabor/functions/schedule/handle_sessions.php
(will be executed every 5 minutes between 10am and 11pm everyday)
*/

$compare_start = date("Y-m-d H:i:s");
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));
$compare_close = date("Y-m-d H:i:s", strtotime($compare_start.'+30MINUTES'));

try{
	$db->beginTransaction();
	// Opens to records session that will begin in the next 90 minutes.
	$sessions = $db->query("SELECT cours_id FROM cours WHERE cours_start <= '$compare_end' AND cours_start >= '$compare_start'");
	/*$update = $db->prepare("UPDATE cours SET ouvert=1 WHERE cours_start<=? AND cours_start>=?");
	$update->bindParam(1, $compare_end);
	$update->bindParam(2, $compare_start);
	$update->execute();*/
	while($session = $sessions->fetch(PDO::FETCH_ASSOC)){
		$session_id = $session["cours_id"];
		$open = $db->query("UPDATE cours SET ouvert = 1 WHERE cours_id='$session_id'");
		$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_date, notification_state)
								VALUES('SES', '$session_id', '$compare_start', '1')");
	}


	// Leaves the sesssions open but doesn't accept records anymore for sessions that will end in the next 30 minutes.
	$partial_close = $db->query("UPDATE cours SET ouvert = 2 WHERE cours_end <= '$compare_close' AND ouvert = 1");
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
