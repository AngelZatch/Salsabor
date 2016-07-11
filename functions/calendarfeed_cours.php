<?php
require_once "db_connect.php";
/** Feeding sessions to the calendar **/
try
{

	$db = PDOFactory::getConnection();
	/** Obtention des cours **/
	$calendar = $db->prepare('SELECT cours_id, cours_intitule, room_id, cours_start, cours_end, color_value FROM cours c
							JOIN rooms r ON c.cours_salle = r.room_id
							JOIN colors co ON r.room_color = co.color_id');
	$calendar->execute();
	$events = array();

	/** Remplissage récursif d'un tableau et encodage JSON **/
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
