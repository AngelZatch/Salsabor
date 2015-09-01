<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
$status = "1";
$rfid = $db->prepare('SELECT passage_eleve FROM passages WHERE status=?');
$rfid->bindParam(1, $status);
$rfid->execute();
$res = $rfid->fetch(PDO::FETCH_ASSOC);
echo $res["passage_eleve"];
?>
