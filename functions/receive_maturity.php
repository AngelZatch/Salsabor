<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

/** Marks a maturity as paiement received **/

$id = $_POST["maturity_id"];

if(isset($_POST["date"])){
	$date_reception = $_POST["date"];
	$method = $_POST["method"];
	$stmt = $db->prepare("UPDATE produits_echeances SET echeance_effectuee = '1', date_paiement = ?, methode_paiement = ? WHERE produits_echeances_id=?");
	$stmt->bindParam(1, $date_reception, PDO::PARAM_STR);
	$stmt->bindParam(2, $method, PDO::PARAM_STR);
	$stmt->bindParam(3, $id, PDO::PARAM_INT);
	$stmt->execute();
} else {
	$date = $db->query("SELECT date_echeance FROM produits_echeances WHERE produits_echeances_id = '$id'")->fetch(PDO::FETCH_COLUMN);
	$update = $db->query("UPDATE produits_echeances SET echeance_effectuee = '0', date_paiement = NULL WHERE produits_echeances_id = '$id'");
	echo $date;
}
?>
