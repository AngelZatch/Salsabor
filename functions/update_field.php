<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$datatable = $_POST["database"];
$token = explode('-', $_POST["token"]);
$field = $token[0];
$id = $token[1];
$value = $_POST["value"];

try{
	$db->beginTransaction();
	$stmt = $db->prepare("UPDATE $datatable SET $field = ? WHERE ".$datatable."_id = ?");
	$stmt->bindParam(1, $value);
	$stmt->bindParam(2, $id);
	$stmt->execute();
	$db->commit();
	echo "Modification enregistrée";
}catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
