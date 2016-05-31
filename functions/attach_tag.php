<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_POST["tag"]) && isset($_POST["target"]) && isset($_POST["type"])){
	$tag = intval($_POST["tag"]);
	$target = $_POST["target"];
	$type = $_POST["type"];

	associateTag($db, $tag, $target, $type);
}

function associateTag($db, $tag, $target, $type){
	if(is_string($tag)){
		$tag = $db->query("SELECT rank_id FROM tags_user WHERE rank_name='$tag'")->fetch(PDO::FETCH_COLUMN);
	}

	$query = "INSERT IGNORE INTO assoc_".$type."_tags(".$type."_id_foreign, tag_id_foreign) VALUES($target, $tag)";

	$attach = $db->query($query);

	echo $db->lastInsertId();
	return $db->lastInsertId();
}
?>
