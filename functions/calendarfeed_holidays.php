<?php
require_once "db_connect.php";
// Feeding holidays to the calendar
$db = PDOFactory::getConnection();
$fetch_start = $_GET["fetch_start"];
$fetch_end = $_GET["fetch_end"];
try{
	// Fetching holidays
	$calendar = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee >= '$fetch_start' AND date_chomee < '$fetch_end'");
	$calendar->execute();
	$events = array();

	/** Remplissage récursif d'un tableau et encodage JSON **/
	while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
		$e = array();
		$e['id'] = $row_calendar['jour_chome_id'];
		$e['title'] = "Jour chômé";
		$e['start'] = $row_calendar['date_chomee']." 06:00:00";
		$e['end'] = $row_calendar['date_chomee']." 24:00:00";
		$e['type'] = "holiday";
		// Paramètre propriétaire de Fullcalendar.js qui sert à délimiter un évènement
		// à ses heures de début et de fin.
		$e['allDay'] = true;

		array_push($events, $e);
	}

	echo json_encode($events);
	exit();
} catch(PDOException $e) {
	echo $e->getMessage();
}
?>
