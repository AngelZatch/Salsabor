<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["echeance_id"];
$date_reception = date_create("now")->format("Y-m-d");

$maturity = $db->query("SELECT date_echeance, echeance_effectuee FROM produits_echeances WHERE produits_echeances_id=$id")->fetch(PDO::FETCH_ASSOC);
if($maturity["echeance_effectuee"] == 1){
	if($maturity["date_echeance"] < $date_reception){
		$newState = 2;
	} else {
		$newState = 0;
	}
	$date_reception = null;
} else {
	$newState = 1;
}

try{
	$db->beginTransaction();
	$update = $db->prepare("UPDATE produits_echeances SET echeance_effectuee=$newState, date_paiement='$date_reception' WHERE produits_echeances_id=?");
	$update->bindParam(1, $id);
	$update->execute();
	$db->commit();
	echo $newState;
} catch (PDOExecption $e) {
	$db->rollBack();
	var_dump($e->getMessage());
}

?>
