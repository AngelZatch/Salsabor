<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$session_id = $_GET['session_id'];
$session_details = $db->query("SELECT s.cours_id, cours_intitule, cours_start, cours_end, room_name, color_value, COUNT(passage_id) AS participations_count FROM cours s
							JOIN users u ON s.prof_principal = u.user_id
							JOIN rooms r ON s.cours_salle = r.room_id
							JOIN colors co ON r.room_color = co.color_id
							JOIN participations pr ON s.cours_id = pr.cours_id
							WHERE s.cours_id='$session_id'")->fetch(PDO::FETCH_ASSOC);

$s = array(
	"id" => $session_details["cours_id"],
	"title" => $session_details["cours_intitule"],
	"start" => $session_details["cours_start"],
	"end" => $session_details["cours_end"],
	"room" => $session_details["room_name"],
	"color" => "#".$session_details["color_value"],
	"participations_count" => $session_details["participations_count"]
);

echo json_encode($s);
?>
