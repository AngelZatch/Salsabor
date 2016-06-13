<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$name = htmlspecialchars($_POST["location_name"]);

try{
	$db->beginTransaction();
	$create = $db->prepare("INSERT INTO locations(location_name) VALUES(:name)");
	$create->bindParam(':name', $name, PDO::PARAM_STR);
	$create->execute();
	echo $db->lastInsertId();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
