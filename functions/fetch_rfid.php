<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();

$time = date("Y-m-d H:i:s");
$status = "1";

$rfid = $db->prepare('SELECT user_rfid FROM participations WHERE status=? AND passage_date > ?');
$rfid->bindParam(1, $status);
$rfid->bindParam(2, $time);
$rfid->execute();

echo $rfid->fetch(PDO::FETCH_COLUMN);
?>
