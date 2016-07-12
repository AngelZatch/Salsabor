<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$task_creator_id = null;

if(isset($_POST["task_title"]) && isset($_POST["task_description"]) && isset($_POST["task_token"])){
	$task_title = $_POST["task_title"];
	$task_description = $_POST["task_description"];
	$task_token = $_POST["task_token"];

	if(isset($_POST["task_creator_id"]))
		$task_creator_id = $_POST["task_creator_id"];

	createTask($db, $task_title, $task_description, $task_token, $task_creator_id);
}

function createTask($db, $task_title, $task_description, $task_token, $task_creator_id){
	preg_match('/\\[([a-z0-9\\-]+)\\]/i', $task_token, $matches);
	$task_title = htmlspecialchars($task_title);
	$task_description = htmlspecialchars($task_description, ENT_QUOTES | ENT_HTML5);
	// Before posting the task, we must figure out the token.
	$token = substr($matches[1], 0, 3);
	$target = substr($matches[1], 4);

	try{
		$stmt = $db->prepare("INSERT INTO tasks(task_token, task_target, task_title, task_description, task_creator)
							VALUES(:token, :target, :title, :description, :creator_id)");
		$stmt->bindValue(":token", $token, PDO::PARAM_STR);
		$stmt->bindValue(":target", $target, PDO::PARAM_INT);
		$stmt->bindValue(":title", $task_title, PDO::PARAM_STR);
		$stmt->bindValue(":description", $task_description, PDO::PARAM_STR);
		if(isset($task_creator_id))
			$stmt->bindValue(":creator_id", $task_creator_id, PDO::PARAM_INT);
		else
			$stmt->bindValue(":creator_id", NULL, PDO::PARAM_INT);
		$stmt->execute();
		echo $db->lastInsertId();
		return $db->lastInsertId();
	} catch(PDOException $e){
		echo $e->getMessage();
	}
}
?>
