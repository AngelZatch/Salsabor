<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();

$type = $_GET["type"];

$stmt = $db->query("SELECT * FROM tags_$type");

$tags = array();
while($tag = $stmt->fetch(PDO::FETCH_ASSOC)){
	$t = array();
	$t["rank_id"] = $tag["rank_id"];
	$t["rank_name"] = $tag["rank_name"];
	$t["color"] = $tag["tag_color"];
	if($type == 'user')
		$t["mid"] = $tag["missing_info_default"];
	array_push($tags, $t);
}

echo json_encode($tags);
?>
