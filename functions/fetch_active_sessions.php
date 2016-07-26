<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$compare_start = date("Y-m-d H:i:s");
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));
if(!isset($_GET["fetched"])){
	$load = $db->query("SELECT * FROM sessions s
								JOIN rooms r ON s.session_room = r.room_id
								JOIN users u ON s.session_teacher = u.user_id
								WHERE session_opened != 0
								ORDER BY session_start ASC, session_id ASC");
} else {
	$fetched = $_GET["fetched"];
	$load = $db->query("SELECT * FROM sessions s
								JOIN rooms r ON s.session_room = r.room_id
								LEFT JOIN users u ON s.session_teacher = u.user_id
								WHERE session_opened != 0 AND session_id NOT IN ('".implode($fetched, "','")."')
								ORDER BY session_start ASC, session_id ASC");
}

$sessionsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$s = array();
	$s["id"] = $details["session_id"];
	$s["title"] = $details["session_name"];
	$s["start"] = $details["session_start"];
	$s["end"] = $details["session_end"];
	$s["duration"] = $details["session_duration"];
	$s["room"] = $details["room_name"];
	$s["teacher"] = $details["user_prenom"]." ".$details["user_nom"];
	// Tags
	$labels = $db->query("SELECT * FROM assoc_session_tags us
						JOIN tags_session ts ON us.tag_id_foreign = ts.rank_id
						WHERE session_id_foreign = '$s[id]'
						ORDER BY tag_color DESC");
	$s["labels"] = array();
	while($label = $labels->fetch(PDO::FETCH_ASSOC)){
		$l = array();
		$l["entry_id"] = $label["entry_id"];
		$l["tag_color"] = $label["tag_color"];
		$l["rank_name"] = $label["rank_name"];
		$l["is_mandatory"] = $label["is_mandatory"];
		array_push($s["labels"], $l);
	}
	array_push($sessionsList, $s);
}

echo json_encode($sessionsList);
?>
