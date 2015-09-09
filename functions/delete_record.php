<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$passage_id = $_POST["passage_id"];
$db->query("DELETE FROM passages WHERE passage_id=$passage_id");
?>
