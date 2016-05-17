<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_id = $_GET["user_id"];

$details = $db->query("SELECT mail, user_rfid, telephone, CONCAT(rue, ' - ', code_postal, '', ville) AS address FROM users u WHERE user_id = $user_id")->fetch(PDO::FETCH_ASSOC);

echo json_encode($details);
?>
