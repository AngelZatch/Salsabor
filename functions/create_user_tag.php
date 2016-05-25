<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$name = htmlspecialchars($_POST["name"]);

$create = $db->query("INSERT IGNORE INTO tags_user(rank_name) VALUES('$name')");

echo $db->lastInsertId();
?>
