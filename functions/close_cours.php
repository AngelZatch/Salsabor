<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$cours = $_POST["cours"];

try{
	$db->beginTransaction();
	
	// Fermeture du cours
	$update = $db->query("UPDATE cours SET ouvert=0 WHERE cours_id=$cours");
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>