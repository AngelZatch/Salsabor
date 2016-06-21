<?php
session_start();
require_once "functions/db_connect.php";
require_once "functions/tools.php";
$db = PDOFactory::getConnection();
$user_id = $_SESSION["user_id"];

$data = $_POST['imagebase64'];

list($type, $data) = explode(';', $data);
list(, $data)      = explode(',', $data);
$data = base64_decode($data);

file_put_contents('image64.png', $data);

echo "<img src='image64.png'>";

/*$dest_file = $user_id.".png";
file_put_contents($dest_file, $data);
$target_dir = "assets/pictures/";
$target_file = $target_dir.$dest_file;
move_uploaded_file($dest_file, $target_file);
try{
	$db->beginTransaction();
	$edit = $db->prepare('UPDATE users SET photo = :photo WHERE user_id = :id');
	$edit->bindParam(':photo', $target_file);
	$edit->bindParam(':id', $data);
	$edit->execute();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	var_dump($e->getMessage());
}*/
//$update = $db->query("UPDATE users SET photo = '$data' WHERE user_id = $user_id");
?>
