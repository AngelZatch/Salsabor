<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$task_title = addslashes($_POST["task_title"]);
$task_description = htmlspecialchars($_POST["task_description"], ENT_QUOTES | ENT_HTML5);
$task_token = preg_match('/\\[([a-z0-9\\-]+)\\]/i', $_POST["task_token"], $matches);

// Before posting the task, we must figure out the token.
$token = substr($matches[1], 0, 3);
$target = substr($matches[1], 4);

try{
	$loadProducts = $db->query("INSERT INTO tasks(task_token, task_target, task_title, task_description)
							VALUES('$token', '$target', '$task_title', '$task_description')");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
