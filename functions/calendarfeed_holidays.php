<?php
require_once "db_connect.php";
/** Page servant à alimenter le planning des cours **/
try
{
	
	$db = PDOFactory::getConnection();
    /** Obtention des cours **/
    $calendar = $db->prepare('SELECT * FROM jours_chomes');
    $calendar->execute();
    $events = array();

    /** Remplissage récursif d'un tableau et encodage JSON **/
    while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
        $e = array();
        $e['id'] = $row_calendar['jour_chome_id'];
        $e['title'] = "Jour chômé";
        $e['start'] = $row_calendar['date_chomee']." 09:00:00";
        $e['end'] = $row_calendar['date_chomee']." 24:00:00";
        $e['type'] = "holiday";
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