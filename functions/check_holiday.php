<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();

$date = $_POST["date_debut"];

$search = $db->prepare('SELECT * FROM jours_chomes WHERE date_chomee=?');
$search->bindParam(1, $date);
$search->execute();
echo $search->rowCount();
?>