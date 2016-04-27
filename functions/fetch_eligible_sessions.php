<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$participation_id = $_GET["participation_id"];
// This script will fetch the sessions that WERE available for the participation

$participation = $db->query("SELECT * FROM participations WHERE passage_id = '$participation_id'")->fetch(PDO::FETCH_ASSOC);

$compare_start = date("Y-m-d H:i:s", strtotime($participation["passage_date"].'-90MINUTES'));
$compare_end = date("Y-m-d H:i:s", strtotime($participation["passage_date"].'+90MINUTES'));
$sessions = $db->query("SELECT * FROM cours c
						JOIN salle ON cours_salle=salle.salle_id
						JOIN users ON prof_principal=users.user_id
						JOIN niveau ON cours_niveau=niveau.niveau_id
						WHERE cours_start BETWEEN '$compare_start' AND '$compare_end'");

$session_list = array();
while($details = $sessions->fetch(PDO::FETCH_ASSOC)){
	$s = array();
	$s["id"] = $details["cours_id"];
	$s["title"] = $details["cours_intitule"];
	$s["start"] = $details["cours_start"];
	$s["end"] = $details["cours_end"];
	$s["duration"] = $details["cours_unite"];
	$s["level"] = $details["niveau_name"];
	$s["room"] = $details["salle_name"];
	$s["teacher"] = $details["user_prenom"]." ".$details["user_nom"];
	array_push($session_list, $s);
}

echo json_encode($session_list);
?>
