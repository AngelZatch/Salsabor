<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_id = $_GET["user_id"];

$details = $db->query("SELECT user_prenom, user_nom, mail, user_rfid, telephone, CONCAT(rue, ' - ', code_postal, '', ville) AS address FROM users u WHERE user_id = $user_id")->fetch(PDO::FETCH_ASSOC);

if($details["telephone"] == " "){
	$details["telephone"] = "Ajouter un numéro";
}
if($details["address"] == " - "){
	$details["address"] = "Ajouter une adresse";
}
if($details["user_rfid"] == null){
	$details["user_rfid"] = "Pas de code RFID";
}

echo json_encode($details);
?>
