<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$name = htmlspecialchars($_POST["name"]);
$type = $_POST["type"];

$stmt = $db->prepare("INSERT IGNORE INTO tags_$type(rank_name) VALUES(?)");
$stmt->bindParam(1, $name, PDO::PARAM_STR);
$stmt->execute();

echo $db->lastInsertId();
?>
