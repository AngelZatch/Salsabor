<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$nombrePassages = $db->query("SELECT * FROM participations WHERE status=0 OR status=3")->rowCount();
echo $nombrePassages;
?>
