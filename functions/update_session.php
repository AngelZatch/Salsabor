<?php
// As the generic update entry cannot be used for a session because of all the differences, there's a separare script to handle the update of one or many sessions at once.
require_once "db_connect.php";
require_once "tools.php";
$db = PDOFactory::getConnection();

// The main difference comes from the dates; Modifying them for one session is easy, we just have to overwrite. But if we want to update several sessions at once, the correct way is to modify the dates based on the delta between the old date and the new date; this delta will be applied to all dates of all sessions, making it really easier.

// First, we get the sessions that will be modified.
$sessions = $_POST["sessions"];
// Then, these are all the values that have to be applied to the array of sessions
parse_str($_POST["values"], $values);
// Lastly, we receive the ID of the session the modifications come from
$hook = $_POST["hook"];

// == GET THE DELTA ==
// New values from the serialized array
$new_start = new DateTime($values["cours_start"]);
$new_end = new DateTime($values["cours_end"]);

// Old values from the database and the hook
$hook_times = $db->query("SELECT cours_start, cours_end FROM cours WHERE cours_id = $hook")->fetch(PDO::FETCH_ASSOC);
$old_start = new DateTime($hook_times["cours_start"]);
$old_end = new DateTime($hook_times["cours_end"]);

// We calculate the delta
$start_delta = $old_start->diff($new_start);
$end_delta = $old_end->diff($new_end);

// == QUERY ==
for($i = 0; $i < sizeof($sessions); $i++){
	// We fetch the times from each session
	$session_times = $db->query("SELECT cours_start, cours_end FROM cours WHERE cours_id = $sessions[$i]")->fetch(PDO::FETCH_ASSOC);
	try{
		$query = "UPDATE cours SET ";
		foreach($values as $row => $value){
			// If we have to get a name
			if($row == "prof_principal")
				$value = solveAdherentToId($value);

			// We apply the delta
			if($row == "cours_start"){
				$value = new DateTime($session_times["cours_start"]);
				if($new_start < $value){
					$value->sub(new DateInterval("P".$start_delta->format("%d")."DT".$start_delta->format("%h")."H".$start_delta->format("%i")."M"));
				} else {
					$value->add(new DateInterval("P".$start_delta->format("%d")."DT".$start_delta->format("%h")."H".$start_delta->format("%i")."M"));
				}
				$value = $value->format("Y-m-d H:i:s");
			}

			if($row == "cours_end"){
				$value = new DateTime($session_times["cours_end"]);
				if($new_end < $value){
				$value->sub(new DateInterval("P".$end_delta->format("%d")."DT".$end_delta->format("%h")."H".$end_delta->format("%i")."M"));
				} else {
				$value->add(new DateInterval("P".$end_delta->format("%d")."DT".$end_delta->format("%h")."H".$end_delta->format("%i")."M"));
				}
				$value = $value->format("Y-m-d H:i:s");
			}

			$query .= "$row = '$value'";
			if($row !== end(array_keys($values))){
				$query .= ", ";
			}
		}
		$query .= " WHERE cours_id = '$sessions[$i]'";
		echo $query."\n";
		$db->beginTransaction();
		$update = $db->query($query);
		$db->commit();
	} catch(PDOException $e){
		$db->rollBack();
		echo $e->getMessage();
	}
}
