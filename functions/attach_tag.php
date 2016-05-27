<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$tag = $_POST["tag"];
$target = $_POST["target"];
$type = $_POST["type"];

$query = "INSERT IGNORE INTO assoc_".$type."_tags(".$type."_id_foreign, tag_id_foreign) VALUES($target, $tag)";

$attach = $db->query($query);

echo $db->lastInsertId();
?>
