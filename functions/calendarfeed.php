<?php
/** Page servant à alimenter le planning des cours **/
try
{
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    /** Obtention des cours **/
    $calendar = $db->prepare('SELECT * FROM cours');
    $calendar->execute();
    $events = array();

    /** Remplissage récursif d'un tableau et encodage JSON **/
    while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
        $e = array();
        $e['id'] = $row_calendar['cours_id'];
        $e['title'] = $row_calendar['cours_intitule'];
        // La date de début sert à délimiter la durée réelle d'un SEUL cours. Il est
        // ensuite répété par un script js qui le répète à une fréquence hebdomadaire
        // jusqu'à la date de fin, en respectant toutes les données.
        $e['start'] = $row_calendar['cours_start'];
        $e['end'] = $row_calendar['cours_end'];
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