<?php
require_once 'db_connect.php';
$db = PDOFactory::getConnection();

$user_id = $_POST["user_id"];
$location = $_POST["location"];

// Since this file is called regardless of the updated file, the main key is variable
$file_key = array_keys($_FILES)[0];

// Source file
$source_file = $_FILES[$file_key]["tmp_name"];

// File destination
$new_file = $location.$user_id.".".pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION);

move_uploaded_file($source_file, $new_file);

// We update the database as well
$update = $db->query("UPDATE users SET $file_key = '$new_file' WHERE user_id = $user_id");

echo json_encode($file_key);
?>
