<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$session_id = $_POST["session_id"];
$user_id = $_POST["user_id"];
$record_id = $_POST["record_id"];

/** This code will set the status of the record to 1 or 3 again, depending on whether there are products for the user, and delete the participation. Once it's done, we'll "Compute" the product to refresh its data.**/

// First, we delete the participation
$product_id = $db->query("SELECT produit_adherent_id FROM cours_participants WHERE cours_id_foreign = '$session_id' AND eleve_id_foreign = '$user_id'")->fetch(PDO::FETCH_COLUMN);
$deleteParticipation = $db->query("DELETE FROM cours_participants WHERE cours_id_foreign = '$session_id' AND eleve_id_foreign = '$user_id'");

if($product_id == null){
	$status = '3';
} else {
	$status = '0';
}

// Update the record as handled with the correct session and status
$update = $db->query("UPDATE passages SET cours_id=NULL, status='$status' WHERE passage_id='$record_id'");

echo $product_id;
?>
