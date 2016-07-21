<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$holiday_date = $_POST["holiday_date"];
$duration = $_POST["duration"];
$action = $_POST["action"];

for($i = 0; $i < $duration; $i++){
	$initial_date = new DateTime($holiday_date);
	$insert_date = $initial_date->add(new dateinterval("P".$i."D"))->format("Y-m-d");
	try{
		// Depending of the action, the query changes to insert or delete holidays
		if($action == "post")
			$query = "INSERT INTO jours_chomes(date_chomee) VALUES(?)";
		else
			$query = "DELETE FROM jours_chomes WHERE date_chomee = ?";

		$stmt = $db->prepare($query);
		$stmt->bindParam(1, $insert_date, PDO::PARAM_STR);
		$stmt->execute();
	} catch(PDOException $e){
		echo $e->getMessage();
	}
}

?>
