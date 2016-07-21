<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$check_date = $_GET["check_date"];

$value = $db->query("SELECT jour_chome_id FROM jours_chomes WHERE date_chomee = '$check_date'");

$holiday_id = $value->fetch(PDO::FETCH_COLUMN);

echo ($holiday_id!=null)?$holiday_id:-1;

?>
