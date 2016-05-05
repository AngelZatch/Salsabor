<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
$status = "1";
$rfid = $db->prepare('SELECT user_rfid FROM participations WHERE status=?');
$rfid->bindParam(1, $status);
$rfid->execute();
$res = $rfid->fetch(PDO::FETCH_ASSOC);
echo $res["user_rfid"];
?>
