<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$session_id = $_GET["session_id"];

$session = $db->query("SELECT cours_salle, cours_start
					FROM cours c
					WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_ASSOC);

$limit_start = date("Y-m-d H:i:s", strtotime($session["cours_start"].'-30MINUTES'));
$limit_end = date("Y-m-d H:i:s", strtotime($session["cours_start"].'+30MINUTES'));

$load = $db->query("SELECT * FROM passages p
					JOIN lecteurs_rfid lr ON p.passage_salle = lr.lecteur_ip
					JOIN users u ON p.passage_eleve = u.user_rfid OR p.passage_eleve = u.user_id
					WHERE ((status = '0' OR status = '3') AND lecteur_lieu = '$session[cours_salle]' AND passage_date >= '$limit_start' AND passage_date <= '$limit_end') OR (status = '2' AND cours_id = '$session_id')
					ORDER BY user_nom ASC");

$recordsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$r = array();
	$r["id"] = $details["passage_id"];
	$r["card"] = $details["passage_eleve"];
	$r["user"] = $details["user_prenom"]." ".$details["user_nom"];
	$r["date"] = $details["passage_date"];
	$r["status"] = $details["status"];
	array_push($recordsList, $r);
}

echo json_encode($recordsList);
?>
