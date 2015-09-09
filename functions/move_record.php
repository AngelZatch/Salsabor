<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$passage_id = $_POST["passage_id"];
$cours_id = $_POST["target_id"];
$db->query("UPDATE passages SET cours_id=$cours_id, status=2 WHERE passage_id=$passage_id");
echo "Passage déplacé et validé";
?>
