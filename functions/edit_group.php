<?php
require_once "db_connect.php";
include "cours.php";
include "fetch_session_group.php";

$db = PDOFactory::getConnection();
$group_id = $_POST["group_id"];
$delta_steps = $_POST["delta_steps"];

/* Depending on the value of delta_steps, we will have to add or remove sessions from the group.
*/
if($delta_steps > 0){ // Add sessions to the group
	// We get all the details from the group
	$group_details = fetchGroupDetails($db, $group_id);
	$session_name = $group_details["parent_intitule"];
	$teacher_id = $group_details["group_teacher"];
	$room_id = $group_details["parent_salle"];
	$session_duration = $group_details["parent_unite"];
	$hour_fee = $group_details["parent_cout_horaire"];

	// To avoid having wrong dates, we get the date of the last session of the group
	$last_session_date = $db->query("SELECT session_start, session_end FROM sessions WHERE session_group = $group_id ORDER BY session_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

	for($i = 1; $i <= $delta_steps; $i++){
		// We get the new dates of the sessions
		$days_added = $i * 7;
		$start_date = strtotime($last_session_date["session_start"].'+'.$days_added.'DAYS');
		$end_date = strtotime($last_session_date["session_end"].'+'.$days_added.'DAYS');
		$start = date("Y-m-d H:i:s", $start_date);
		$end = date("Y-m-d H:i:s", $end_date);
		if($i == 1){
			$next_session = createSession($db, $group_id, $session_name, $start, $end, $teacher_id, $room_id, $session_duration, $hour_fee, 2);
			echo $next_session;
		} else {
			createSession($db, $group_id, $session_name, $start, $end, $teacher_id, $room_id, $session_duration, $hour_fee, 2);
		}
		if($i == $delta_steps){
			// Once all the sessions have been applied, we update the date of the end of recurrence to the group
			$new_recurrence_end = date("Y-m-d", $end_date);
			updateRecurrenceEndDate($db, $group_id, $new_recurrence_end);
		}
	}
}
if($delta_steps < 0){ // Remove sessions from the group
	$limit = -1 * $delta_steps;
	$sessions_to_delete = $db->query("SELECT session_id FROM sessions WHERE session_group = $group_id ORDER BY session_id DESC LIMIT $limit")->fetchAll(PDO::FETCH_COLUMN);

	foreach($sessions_to_delete as $session_id){
		$db->query("DELETE FROM sessions WHERE session_id = $session_id");
	}
	$last_session = $db->query("SELECT session_end, session_id FROM sessions WHERE session_group = $group_id ORDER BY session_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
	$end_date = new DateTime($last_session["session_end"]);
	$new_recurrence_end = $end_date->format("Y-m-d");
	updateRecurrenceEndDate($db, $group_id, $new_recurrence_end);
	echo $last_session["session_id"];
}

?>
