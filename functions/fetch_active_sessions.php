<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$compare_start = date("Y-m-d H:i:s");
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));
if(!isset($_GET["fetched"])){
	$load = $db->query("SELECT * FROM cours
								JOIN rooms r ON cours_salle = r.room_id
								JOIN users ON prof_principal=users.user_id
								JOIN niveau ON cours_niveau=niveau.niveau_id
								WHERE ouvert != 0
								ORDER BY cours_start ASC, cours_id ASC");
} else {
	$fetched = $_GET["fetched"];
	$load = $db->query("SELECT * FROM cours
								JOIN rooms ON cours_salle = r.room_id
								JOIN users ON prof_principal=users.user_id
								JOIN niveau ON cours_niveau=niveau.niveau_id
								WHERE ouvert != 0 AND cours_id NOT IN ('".implode($fetched, "','")."')
								ORDER BY cours_start ASC, cours_id ASC");
}

$sessionsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$s = array();
	$s["id"] = $details["cours_id"];
	$s["title"] = $details["cours_intitule"];
	$s["start"] = $details["cours_start"];
	$s["end"] = $details["cours_end"];
	$s["duration"] = $details["cours_unite"];
	$s["level"] = $details["niveau_name"];
	$s["room"] = $details["room_name"];
	$s["teacher"] = $details["user_prenom"]." ".$details["user_nom"];
	// Tags
	$labels = $db->query("SELECT * FROM assoc_session_tags us
						JOIN tags_session ts ON us.tag_id_foreign = ts.rank_id
						WHERE session_id_foreign = '$s[id]'");
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
