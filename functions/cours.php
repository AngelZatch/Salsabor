<?php
require_once "db_connect.php";
require_once "tools.php";
/** ADD COURS **/
function addCours(){
	$db = PDOFactory::getConnection();

	$session_name = $_POST['intitule'];
	$user_id = solveAdherentToId($_POST["prof_principal"]);
	$room_id = $_POST['lieu'];

	// Times
	$start = $_POST["cours_start"];
	$end = $_POST["cours_end"];
	$weekday = date('N', strtotime($start));

	// Computing duration of the session(s)
	$session_duration = (strtotime($end) - strtotime($start))/3600;

	if($_POST['recurrence'] == 0){ // No recurrence
		try{
			$db->beginTransaction();
			/** Inserting parent **/
			$parent_id = insertParent($db, $session_name, $weekday, $start, $end, $user_id, $room_id, $session_duration, 0, 0, 0, 2);

			/** Inserting lone child **/
			$session_id = createSession($db, $parent_id, $session_name, $start, $end, $user_id, $room_id, $session_duration, 0, 2);

			$db->commit();
			header("Location: cours/$session_id");
		} catch(PDOException $e){
			$db->rollBack();
			var_dump($e->getMessage());
		}
	} else { // Recurrence
		$recurrence = $_POST['recurrence'];
		$frequency = 7; // By default, weekly recurrence
		$recurrence_steps = $_POST["steps"];
		// Computing end date and hour
		$end_hour = new DateTime($end);
		$end_hour = $end_hour->format("H:i:s");
		$recurrence_stop = $_POST['date_fin']." ".$end_hour;
		try{
			$db->beginTransaction();

			/** Inserting parent **/
			$parent_id = insertParent($db, $session_name, $weekday, $start, $recurrence_stop, $user_id, $room_id, $session_duration, 0, $recurrence, $frequency, 2);

			for($i = 1; $i < $recurrence_steps; $i++){
				// Inserting session
				if($i == 1)
					$first_session_id = createSession($db, $parent_id, $session_name, $start, $end, $user_id, $room_id, $session_duration, 0, 2);
				else
					createSession($db, $parent_id, $session_name, $start, $end, $user_id, $room_id, $session_duration, 0, 2);

				// Changing dates for next one
				$start_date = strtotime($start.'+'.$frequency.'DAYS');
				$end_date = strtotime($end.'+'.$frequency.'DAYS');
				$start = date("Y-m-d H:i:s", $start_date);
				$end = date("Y-m-d H:i:s", $end_date);

			}
			$db->commit();
			header("Location: cours/$first_session_id");
		} catch(PDOException $e){
			$db->rollBack();
			var_dump($e->getMessage());
		}
	}
}

function insertParent($db, $session_name, $weekday, $start, $end, $user_id, $room_id, $session_duration, $hour_fee, $recurrence, $frequency, $priorite){
	// Formats
	$start_date = new DateTime($start);
	$start_date = $start_date->format("Y-m-d");

	$end_date = new DateTime($end);
	$end_date = $end_date->format("Y-m-d");

	$start_hour = new DateTime($start);
	$start_hour = $start_hour->format("H:i:s");

	$end_hour = new DateTime($end);
	$end_hour = $end_hour->format("H:i:s");

	// Insert into parent
	$insertCours = $db->prepare('INSERT INTO cours_parent(parent_intitule, weekday, parent_start_date, parent_end_date, parent_start_time, parent_end_time, parent_prof_principal, parent_salle, parent_unite, parent_cout_horaire, recurrence, frequence_repetition, priorite)
			VALUES(:intitule, :weekday, :date_debut, :date_fin, :heure_debut, :heure_fin, :prof_principal, :lieu, :unite, :cout_horaire, :recurrence, :frequence_repetition, :priorite)');
	$insertCours->bindParam(':intitule', $session_name);
	$insertCours->bindParam(':weekday', $weekday);
	$insertCours->bindParam(':date_debut', $start_date);
	$insertCours->bindParam(':date_fin', $end_date);
	$insertCours->bindParam(':heure_debut', $start_hour);
	$insertCours->bindParam(':heure_fin', $end_hour);
	$insertCours->bindParam(':prof_principal', $user_id);
	$insertCours->bindParam(':lieu', $room_id);
	$insertCours->bindParam(':unite', $session_duration);
	$insertCours->bindParam(':cout_horaire', $hour_fee);
	$insertCours->bindParam(':recurrence', $recurrence);
	$insertCours->bindParam(':frequence_repetition', $frequency);
	$insertCours->bindParam(':priorite', $priorite);

	$insertCours->execute();
	$parent_id = $db->lastInsertId();
	return $parent_id;
}

function createSession($db, $parent_id, $session_name, $start, $end, $user_id, $room_id, $session_duration, $hour_fee, $priorite){
	$insertCours = $db->prepare('INSERT INTO cours(cours_parent_id, cours_intitule, cours_start, cours_end, prof_principal, cours_salle, cours_unite, cours_prix, priorite)
			VALUES(:cours_parent_id, :intitule, :cours_start, :cours_end, :prof_principal, :lieu, :unite, :cout_horaire, :priorite)');
	$insertCours->bindParam(':cours_parent_id', $parent_id);
	$insertCours->bindParam(':intitule', $session_name);
	$insertCours->bindParam(':cours_start', $start);
	$insertCours->bindParam(':cours_end', $end);
	$insertCours->bindParam(':prof_principal', $user_id);
	$insertCours->bindParam(':lieu', $room_id);
	$insertCours->bindParam(':unite', $session_duration);
	$insertCours->bindParam(':cout_horaire', $hour_fee);
	$insertCours->bindParam(':priorite', $priorite);
	$insertCours->execute();

	$session_id = $db->lastInsertId();
	return $session_id;
}
?>
