<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$tag = $_POST["tag"];
$user_id = $_POST["user"];

$entry_id = $db->query("SELECT entry_id FROM user_ranks WHERE user_id_foreign = $user_id AND rank_id_foreign = $tag")->fetch(PDO::FETCH_COLUMN);

$attach = $db->query("DELETE FROM user_ranks WHERE user_id_foreign = $user_id AND rank_id_foreign = $tag");

echo $entry_id;
?>
