<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$unread = $db->query("SELECT * FROM team_notifications WHERE notification_state = 1")->rowCount();
echo $unread;
?>
