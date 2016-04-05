<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d H:i:s');
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));
$compare_close = date("Y-m-d H:i:s", strtotime($compare_start).'+30MINUTES');

try{
	$db->beginTransaction();
	// Opens to records session that will begin in the next 90 minutes.
	$update = $db->prepare("UPDATE cours SET ouvert=1 WHERE cours_start<=? AND cours_start>=?");
	$update->bindParam(1, $compare_end);
	$update->bindParam(2, $compare_start);
	$update->execute();

	// Leaves the sesssions open but doesn't accept records anymore for sessions that will end in the next 30 minutes.
	$partial_close = $db->query("UPDATE cours SET ouvert = 2 WHERE cours_end <= '$compare_close'");
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
