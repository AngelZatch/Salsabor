<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

/** This code will set the status of the record to 1 or 3 again, depending on whether there are products for the user, and delete the participation. Once it's done, we'll "Compute" the product to refresh its data.**/

$record_id = $_POST["record_id"];

if(isset($_POST["session_id"])){
	$session_id = $_POST["session_id"];
}
if(isset($_POST["user_id"])){
	$user_id = $_POST["user_id"];
}
if(!isset($_POST["user_id"]) || !isset($_POST["session_id"])){
	$record_detais = $db->query("SELECT passage_eleve_id, cours_id, produit_adherent_cible FROM passages WHERE passage_id = '$record_id'")->fetch(PDO::FETCH_ASSOC);
	$user_id = $record_detais["passage_eleve_id"];
	$session_id = $record_detais["cours_id"];
	$product_id = $record_detais["produit_adherent_cible"];
}

// First, we delete the participation
$deleteParticipation = $db->query("DELETE FROM cours_participants WHERE cours_id_foreign = '$session_id' AND eleve_id_foreign = '$user_id'");

$s = array();
if(!isset($product_id)){
	$status = '3';
	$s["product_id"] = null;
} else {
	$status = '0';
	$s["product_id"] = $product_id;
}

// Update the record as handled with the correct session and status
$update = $db->query("UPDATE passages SET status='$status' WHERE passage_id='$record_id'");

$s["status"] = $status;
echo json_encode($s);
?>
