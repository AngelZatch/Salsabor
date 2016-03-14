<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d H:i:s');
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));

try{
	$db->beginTransaction();
	$update = $db->prepare("UPDATE cours SET ouvert=1 WHERE cours_start<=? AND cours_start>=?");
	$update->bindParam(1, $compare_end);
	$update->bindParam(2, $compare_start);
	$update->execute();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
