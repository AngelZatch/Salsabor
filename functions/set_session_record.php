<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];
$session_id = $_POST["session_id"];

$assign = $db->query("UPDATE passages SET cours_id = '$session_id' WHERE passage_id = '$record_id'");

$load = $db->query("SELECT cours_intitule FROM cours c WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_COLUMN);

echo $load;
?>
