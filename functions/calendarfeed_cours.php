<?php
require_once "db_connect.php";
// Feeding sessions to the calendar
$db = PDOFactory::getConnection();
$fetch_start = $_GET["fetch_start"];
$fetch_end = $_GET["fetch_end"];
try{
	// Fetching sessions
	$calendar = $db->prepare("SELECT cours_id, cours_intitule, room_id, cours_start, cours_end, color_value FROM cours c
							JOIN rooms r ON c.cours_salle = r.room_id
							JOIN colors co ON r.room_color = co.color_id
							WHERE cours_start > '$fetch_start' AND cours_end < '$fetch_end'");
	$calendar->execute();
	$events = array();

	while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
		$e = array();
		$e['id'] = $row_calendar['cours_id'];
		$e["title"] = $row_calendar["cours_intitule"];
		$e['lieu'] = $row_calendar['room_id'];
		$e['start'] = $row_calendar['cours_start'];
		$e['end'] = $row_calendar['cours_end'];
		$e['color'] = $row_calendar['color_value'];
		$e['type'] = 'cours';
		// Fullcalendar.js parameter
		$e['allDay'] = false;

		array_push($events, $e);
	}

	echo json_encode($events);
	exit();
} catch(PDOException $e) {
	echo $e->getMessage();
}
?>
