<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$participation_id = $_POST["participation_id"];
$session_id = $_POST["session_id"];

$assign = $db->query("UPDATE participations SET cours_id = '$session_id' WHERE passage_id = '$participation_id'");

$load = $db->query("SELECT cours_intitule, cours_start, cours_end FROM cours c WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_ASSOC);

$s = array();
$s["cours_name"] = $load["cours_intitule"];
$s["cours_start"] = $load["cours_start"];
$s["cours_end"] = $load["cours_end"];

echo json_encode($s);
?>
