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
	$update = $db->query("UPDATE $datatable SET $field = '$value' WHERE ".$datatable."_id = $id");
	$db->commit();
	echo "Modification enregistrée";
}catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
