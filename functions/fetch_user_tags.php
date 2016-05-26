<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();

$user_tags = $db->query("SELECT * FROM tags_user");

$tags = array();
while($tag = $user_tags->fetch(PDO::FETCH_ASSOC)){
	$t = array();
	$t["rank_id"] = $tag["rank_id"];
	$t["rank_name"] = $tag["rank_name"];
	$t["color"] = $tag["tag_color"];
	array_push($tags, $t);
}

echo json_encode($tags);
?>
