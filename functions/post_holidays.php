<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$holiday_date = $_POST["holiday_date"];
$duration = $_POST["duration"];

for($i = 0; $i < $duration; $i++){
	$initial_date = new DateTime($holiday_date);
	$insert_date = $initial_date->add(new dateinterval("P".$i."D"))->format("Y-m-d");
	try{
		$stmt = $db->prepare("INSERT INTO jours_chomes(date_chomee) VALUES(?)");
		$stmt->bindParam(1, $insert_date, PDO::PARAM_STR);
		$stmt->execute();
	} catch(PDOException $e){
		echo $e->getMessage();
	}
}

?>
