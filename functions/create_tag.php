<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$name = htmlspecialchars($_POST["name"]);
$type = $_POST["type"];

$create = $db->query("INSERT IGNORE INTO tags_$type(rank_name) VALUES('$name')");

echo $db->lastInsertId();
?>
