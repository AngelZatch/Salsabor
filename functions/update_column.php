<?php
include "db_connect.php";
include "tools.php";

$db = PDOFactory::getConnection();
$table = $_POST["table"];
$column = $_POST["column"];
$value = $_POST["value"];
$target_id = $_POST["target_id"];

updateColumn($db, $table, $column, $value, $target_id);
?>
