<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

// Tarifs
$queryHolidays = $db->query("SELECT * FROM jours_chomes ORDER BY date_chomee ASC");
$result = array();
while($holiday = $queryHolidays->fetch(PDO::FETCH_ASSOC)){
	$h = array();
	$h["id"] = $holiday["jour_chome_id"];
	$h["date"] = $holiday["date_chomee"];
	array_push($result, $h);
}
echo json_encode($result);
?>