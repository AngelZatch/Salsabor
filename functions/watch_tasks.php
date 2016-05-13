<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$pendingTasks = $db->query("SELECT * FROM tasks WHERE task_state = 0")->rowCount();
echo $pendingTasks;
?>
