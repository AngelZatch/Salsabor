<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$boolean_name = $_POST["data"]["boolean_name"];
$old_value = $_POST["data"]["old_value"];

if($old_value == 1){
	$new_value = 0;
} else {
	$new_value = 1;
}

try{
	if(isset($_POST["data"]["product_id"])){
		$product_id = $_POST["data"]["product_id"];
		$update = $db->query("UPDATE produits_adherents SET $boolean_name = $new_value WHERE id_produit_adherent = '$product_id'");
	}
	if(isset($_POST["data"]["maturity_id"])){
		$maturity_id = $_POST["data"]["maturity_id"];
		$update = $db->query("UPDATE produits_echeances SET $boolean_name = $new_value WHERE produits_echeances_id = '$maturity_id'");
	}
	if(isset($_POST["data"]["notification_id"])){
		$notification_id = $_POST["data"]["notification_id"];
		$update = $db->query("UPDATE team_notifications SET $boolean_name = $new_value WHERE notification_id = '$notification_id'");
	}
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
