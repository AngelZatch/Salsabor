<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

try{
    $db->beginTransaction();
    $update = $db->prepare('UPDATE produits_adherents SET volume_cours=? WHERE id=?');
    $update->bindParam(1, $_POST["remainingHours"]);
    $update->bindParam(2, $_POST["update_id"]);
    $update->execute();
    $db->commit();
}catch(PDOException $e){
    $db->rollBack();
    $message = var_dump($e->getMessage());
	$data = array('type' => 'error', 'message' => ' '.$message);
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
}
?>