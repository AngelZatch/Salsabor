<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$table = $_POST["table"];
$flag = $_POST["flag"];
$value = $_POST["value"];
$target_id = $_POST["target_id"];
$now = date("Y-m-d H:i:s");

try{
	$primary_key = $db->query("SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'")->fetch(PDO::FETCH_ASSOC);

	$update = $db->query("UPDATE $table SET $flag = '$value', task_last_update = '$now' WHERE $primary_key[Column_name] ='$target_id'");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
