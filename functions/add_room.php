<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$room_location = $_POST["room_location"];
$room_name = htmlspecialchars($_POST["room_name"]);

try{
	$db->beginTransaction();
	$create = $db->query("INSERT INTO rooms(room_location, room_name) VALUES('$room_location', '$room_name')");
	echo $db->lastInsertId();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
