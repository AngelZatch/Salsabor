<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$load = $db->query("SELECT * FROM rooms r
					JOIN locations l ON r.room_location = l.location_id
					LEFT JOIN readers re ON r.room_reader = re.reader_id
					ORDER BY location_id, room_name ASC");

$now = date("Y-m-d H:i:s");
$later = date("Y-m-d H:i:s", strtotime($now.'+60MINUTES'));
$rooms = array();
while($room = $load->fetch(PDO::FETCH_ASSOC)){
	$r = array();
	$r["location_id"] = $room["location_id"];
	$r["location_name"] = $room["location_name"];
	$r["location_address"] = $room["location_address"];
	$r["room_id"] = $room["room_id"];
	$r["room_location"] = $room["room_location"];
	$r["room_name"] = $room["room_name"];
	$r["reader_token"] = $room["reader_token"];

	// Look up its availability
	$availability = $db->query("SELECT *, COUNT(*) AS count FROM cours WHERE cours_salle = $r[room_id] AND ((cours_start >= '$now' AND cours_start <= '$later')
	OR (cours_start <= '$now' AND cours_end >= '$now'))")->fetch(PDO::FETCH_ASSOC);
	if($availability["count"] != 0){
		if($availability["cours_start"] < $now){
			$r["availability"] = 0;
			$r["current_session"] = $availability["cours_intitule"];
			$r["current_end"] = $availability["cours_end"];
		} else {
			$r["availability"] = 0.5;
			$r["next_session"] = $availability["cours_intitule"];
			$r["next_start"] = $availability["cours_start"];
		}
	} else {
		$r["availability"] = 1;
	}
	//$r["availability"] = rand(0,1);
	array_push($rooms, $r);
}

echo json_encode($rooms);
?>
