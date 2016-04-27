<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$participation_id = $_POST["participation_id"];
$session_id = $_POST["session_id"];

$assign = $db->query("UPDATE participations SET cours_id = '$session_id' WHERE passage_id = '$participation_id'");

$load = $db->query("SELECT cours_intitule FROM cours c WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_COLUMN);

echo $load;
?>
