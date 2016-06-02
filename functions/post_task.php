<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_POST["task_title"]) && isset($_POST["task_description"]) && isset($_POST["task_token"])){
	$task_title = $_POST["task_title"];
	$task_description = $_POST["task_description"];
	$task_token = $_POST["task_token"];
	createTask($db, $task_title, $task_description, $task_token);
}

function createTask($db, $task_title, $task_description, $task_token){
	preg_match('/\\[([a-z0-9\\-]+)\\]/i', $task_token, $matches);
	$task_title = addslashes($task_title);
	$task_description = htmlspecialchars($task_description, ENT_QUOTES | ENT_HTML5);
	// Before posting the task, we must figure out the token.
	$token = substr($matches[1], 0, 3);
	$target = substr($matches[1], 4);

	try{
		$loadProducts = $db->query("INSERT INTO tasks(task_token, task_target, task_title, task_description)
							VALUES('$token', '$target', '$task_title', '$task_description')");
		echo $db->lastInsertId();
		return $db->lastInsertId();
	} catch(PDOException $e){
		return $e->getMessage();
	}
}
?>
