<?php
require_once "db_connect.php";
// Feeding reservations to the calendar
$db = PDOFactory::getConnection();
$fetch_start = $_GET["fetch_start"];
$fetch_end = $_GET["fetch_end"];
try{
	$calendar = $db->prepare('SELECT * FROM reservations b
							JOIN users u ON b.reservation_personne = u.user_id
							JOIN rooms r ON b.reservation_salle = r.room_id
							JOIN prestations p ON b.type_prestation = p.prestations_id
							WHERE reservation_start > '$fetch_start' AND reservation_end < '$fetch_end'');
	$calendar->execute();
	$events = array();

	/** Remplissage récursif d'un tableau et encodage JSON **/
	while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
		$e = array();
		$e['id'] = $row_calendar['reservation_id'];
		$e['title'] = $row_calendar['prestations_name']." (".$row_calendar['user_prenom']." ".$row_calendar['user_nom']." - ".$row_calendar['room_name'].")";
		$e['lieu'] = $row_calendar['room_id'];
		$e['start'] = $row_calendar['reservation_start'];
		$e['end'] = $row_calendar['reservation_end'];
		$e['type'] = 'reservation';
		$e['priorite'] = $row_calendar['priorite'];
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
