<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$session_id = $_POST["session_id"];

// Fermeture du cours
$update = $db->query("UPDATE cours SET ouvert='0' WHERE cours_id=$session_id");
?>
