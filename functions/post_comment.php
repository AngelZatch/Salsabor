<?php
include "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

$comment = addslashes($_POST["comment"]);
$user_id = $_POST["user_id"];
$task_id = $_POST["task_id"];

try{
	$stmt = $db->prepare("INSERT INTO task_comments(task_id_foreign, task_comment, task_comment_author)
					VALUES(?, ?, ?)");
	$stmt->bindParam(1, $task_id, PDO::PARAM_INT);
	$stmt->bindParam(2, htmlspecialchars($comment), PDO::PARAM_STR);
	$stmt->bindParam(3, $user_id, PDO::PARAM_INT);
	$stmt->execute();
	echo "Message envoyé";
} catch(PDOException $e){
	echo $e->getMessage();
}

?>
