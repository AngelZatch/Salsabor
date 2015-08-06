<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

try{
	$db->beginTransaction();
	$update = $db->prepare('UPDATE tarifs_professeurs SET tarif_prestation=:tarif WHERE tarif_professeur_id=:update_id');
	$update->bindParam(':tarif', $_POST["tarif"]);
	$update->bindParam(':update_id', $_POST["update_id"]);
	$update->execute();
	$db->commit();
	echo "Tarif mis à jour";
} catch (PDOExecption $e) {
	$db->rollBack();
	$message = var_dump($e->getMessage());
	$data = array('type' => 'error', 'message' => ' '.$message);
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
}
?>