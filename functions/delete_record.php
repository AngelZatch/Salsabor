<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

try{
	$db->beginTransaction();
	$delete = $db->prepare('DELETE FROM cours_participants WHERE eleve_id_foreign=?');
	$delete->bindParam(1, $_POST["delete_id"]);
	$delete->execute();
	$db->commit();
} catch (PDOExecption $e) {
	$db->rollBack();
	$message = var_dump($e->getMessage());
}
?>