<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["echeance_id"];
$date_reception = date_create("now")->format("Y-m-d");

try{
	$db->beginTransaction();
	$update = $db->prepare("UPDATE produits_echeances SET echeance_effectuee=1, date_paiement='$date_reception' WHERE produits_echeances_id=?");
	$update->bindParam(1, $id);
	$update->execute();
	$db->commit();
	echo "Echéance mise à jour";
} catch (PDOExecption $e) {
	$db->rollBack();
	var_dump($e->getMessage());
}

?>
