<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
$tag = "192.168.0.4";
$status = "1";
$rfid = $db->prepare('SELECT passage_eleve FROM passages WHERE passage_salle=? AND status=?');
$rfid->bindParam(1, $tag);
$rfid->bindParam(2, $status);
$rfid->execute();
$res = $rfid->fetch(PDO::FETCH_ASSOC);
echo $res["passage_eleve"];
?>