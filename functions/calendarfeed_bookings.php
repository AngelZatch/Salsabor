<?php
require_once "db_connect.php";
// Feeding bookings to the calendar
$db = PDOFactory::getConnection();
$fetch_start = $_GET["fetch_start"];
$fetch_end = $_GET["fetch_end"];
try{
	$calendar = $db->prepare("SELECT *, CONCAT(user_prenom, ' ', user_nom) AS holder FROM reservations b
							JOIN users u ON b.booking_holder = u.user_id
							JOIN rooms r ON b.booking_room = r.room_id
							WHERE booking_start > '$fetch_start' AND booking_end < '$fetch_end'");
	$calendar->execute();
	$events = array();
	while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
		$e = array(
			"id" => $row_calendar["booking_id"],
			"title" => "Réservation (".$row_calendar["holder"].")",
			"lieu" => $row_calendar["room_id"],
			"start" => $row_calendar["booking_start"],
			"end" => $row_calendar["booking_end"],
			"type" => "reservation",
			"color" => "#6ae896",
			"priorite" => $row_calendar["priorite"],
			"allDay" => false
		);

		array_push($events, $e);
	}

	echo json_encode($events);
	exit();
} catch(PDOException $e) {
	echo $e->getMessage();
}
?>
