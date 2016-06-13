<?php
require_once "db_connect.php";
/** Page servant à alimenter le planning des cours **/
try
{

	$db = PDOFactory::getConnection();
	/** Obtention des cours **/
	$calendar = $db->prepare('SELECT * FROM cours c
							JOIN rooms r ON c.cours_salle = r.room_id
							JOIN niveau n ON c.cours_niveau = n.niveau_id
							JOIN prestations p ON c.cours_type = p.prestations_id');
	$calendar->execute();
	$events = array();

	/** Remplissage récursif d'un tableau et encodage JSON **/
	while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
		$e = array();
		$e['id'] = $row_calendar['cours_id'];
		$e['title'] = $row_calendar['cours_intitule']."\n".$row_calendar['room_name']."\n".$row_calendar['niveau_name'];
		$e['lieu'] = $row_calendar['room_id'];
		$e['start'] = $row_calendar['cours_start'];
		$e['end'] = $row_calendar['cours_end'];
		$e['prestation_id'] = $row_calendar['cours_type'];
		$e['prestation'] = $row_calendar['prestations_name'];
		$e['type'] = 'cours';
		// Paramètre propriétaire de Fullcalendar.js qui sert à délimiter un évènement
		// à ses heures de début et de fin.
		$e['allDay'] = false;

		array_push($events, $e);
	}

	echo json_encode($events);
	exit();
} catch(PDOException $e) {
	echo $e->getMessage();
}
?>
