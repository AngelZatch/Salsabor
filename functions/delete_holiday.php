<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

try{
	$db->beginTransaction();
	$delete = $db->prepare('DELETE FROM jours_chomes WHERE jour_chome_id=?');
	$delete->bindParam(1, $_POST["delete_id"]);
	$delete->execute();
	$db->commit();
} catch (PDOExecption $e) {
	$db->rollBack();
	$message = var_dump($e->getMessage());
	$data = array('type' => 'error', 'message' => ' '.$message);
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
}
?>