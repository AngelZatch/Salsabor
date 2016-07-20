<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$holiday_date = $_POST["holiday_date"];

try{
	$stmt = $db->prepare("INSERT INTO jours_chomes(date_chomee) VALUES(?)");
	$stmt->bindParam(1, $holiday_date, PDO::PARAM_STR);
	$stmt->execute();
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
