<?php
require_once "../Salsabor/functions/db_connect.php";
include "../Salsabor/functions/tools.php";
$db = PDOFactory::getConnection();

$data = explode('*', $_GET["carte"]);
$tag_rfid = $data[0];
$ip_rfid = $data[1];
$today = date_create('now')->format('Y-m-d H:i:s');

prepareRecord($db, $tag_rfid, $ip_rfid);

function prepareRecord($db, $tag, $ip){
	if($ip == "192.168.0.3"){
		$status = "1";
		$new = $db->query("INSERT INTO passages(passage_eleve, passage_salle, passage_date, status)
					VALUES('$tag', '$ip', '$today', '$status')");
		echo $ligne = $today.";".$tag.";".$ip."$";
	} else {
		// If the tag is not for associating, we search a product that could be used for this session.
		// First, we get the name of the session and the ID of the user.
		// For the session, we have to find it based on the time of the record and the position.
		$session = $db->query("SELECT cours_intitule, cours_id FROM cours c
								JOIN lecteurs_rfid lr ON c.cours_salle = lr.lecteur_lieu
								WHERE ouvert = '1' AND lecteur_ip = '$ip'")->fetch(PDO::FETCH_GROUP);
		$cours_name = $session["cours_intitule"];
		$session_id = $session["cours_id"];
		$user_id = $db->query("SELECT user_id FROM users WHERE user_rfid = '$tag'")->fetch(PDO::FETCH_COLUMN);

		// Ok, we got everything, let's look for potential duplicates
		$duplicates = $db->query("SELECT * FROM passages WHERE passage_eleve = '$tag' AND cours_id='$session_id'")->rowCount();

		if($duplicates > 0){
			echo $ligne = $today.";".$tag.";".$ip."$-3";
		} else {
			addRecord($db, $cours_name, $session_id, $user_id, $ip, $tag);
		}
	}
}
// The reader expects this:
//echo $ligne = $today.";".$tag_rfid.";".$ip_rfid."$";
