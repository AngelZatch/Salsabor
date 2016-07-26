<?php
require_once "/opt/lampp/htdocs/Salsabor/functions/db_connect.php";
require_once "/opt/lampp/htdocs/Salsabor/functions/tools.php";
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
	$sessions = $db->query("SELECT session_id, session_opened FROM sessions WHERE session_start <= '$compare_end' AND session_start >= '$compare_start'");
	/*$update = $db->prepare("UPDATE cours SET session_opened=1 WHERE session_start<=? AND session_start>=?");
	$update->bindParam(1, $compare_end);
	$update->bindParam(2, $compare_start);
	$update->execute();*/
	while($session = $sessions->fetch(PDO::FETCH_ASSOC)){
		$session_id = $session["session_id"];
		if($session["session_opened"] == 0){
			$open = $db->query("UPDATE cours SET session_opened = 1 WHERE session_id='$session_id'");
			$token = "SES";
			postNotification($db, $token, $session_id, null, $compare_start);
		}
	}

	// Leaves the sesssions open but doesn't accept records anymore for sessions that will end in the next 30 minutes.
	$partial_close = $db->query("UPDATE cours SET session_opened = 2 WHERE session_end <= '$compare_close' AND session_opened = 1");
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
