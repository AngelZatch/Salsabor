<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["echeance_id"];

$maturity = $db->query("SELECT statut_banque FROM produits_echeances WHERE produits_echeances_id=$id")->fetch(PDO::FETCH_ASSOC);
if($maturity["statut_banque"] == 1){
	$newState = 0;
	$date_encaissement = null;
} else {
	$newState = 1;
	$date_encaissement = date_create("now")->format("Y-m-d");
}

try{
	$db->beginTransaction();
	$update = $db->prepare("UPDATE produits_echeances SET statut_banque=$newState,date_encaissement='$date_encaissement' WHERE produits_echeances_id=?");
	$update->bindParam(1, $id);
	$update->execute();
	$db->commit();
	echo $newState;
} catch (PDOExecption $e) {
	$db->rollBack();
	var_dump($e->getMessage());
}
?>
