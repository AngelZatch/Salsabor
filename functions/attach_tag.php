<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$tag = $_POST["tag"];
$user_id = $_POST["user"];

$attach = $db->query("INSERT IGNORE INTO user_ranks(user_id_foreign, rank_id_foreign) VALUES($user_id, $tag)");

echo $db->lastInsertId();
?>
