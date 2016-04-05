<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];
$db->query("DELETE FROM passages WHERE passage_id = '$record_id'");
?>
