<?php
include "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

$comment = addslashes($_POST["comment"]);
$user_id = $_POST["user_id"];
$task_id = $_POST["task_id"];

try{
	$post = $db->query("INSERT INTO task_comments(task_id_foreign, task_comment, task_comment_author)
					VALUES($task_id, '$comment', $user_id)");
	echo "Message envoyé";
} catch(PDOException $e){
	echo $e->getMessage();
}

?>
