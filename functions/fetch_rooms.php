<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$load = $db->query("SELECT * FROM rooms r
					JOIN locations l ON r.room_location = l.location_id
					LEFT JOIN readers re ON r.room_id = re.reader_room
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
	/*$availability = $db->query("SELECT * FROM cours WHERE
	(cours_start > '$now' AND cours_start < '$later' AND cours_salle = $r[room_id])
	OR (cours_start < '$now' AND cours_end > '$now')");
	if($availability->rowCount() > 0){
		$r["availability"] = 1;
	} else {
		$r["availability"] = 0;
	}*/
	$r["availability"] = rand(0,1);
	array_push($rooms, $r);
}

echo json_encode($rooms);
?>
