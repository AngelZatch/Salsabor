<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["echeance_id"];
$date_encaissement = date_create("now")->format("Y-m-d");

try{
	$db->beginTransaction();
	$update = $db->prepare("UPDATE produits_echeances SET statut_banque=1,date_encaissement='$date_encaissement' WHERE produits_echeances_id=?");
	$update->bindParam(1, $id);
	$update->execute();
	$db->commit();
	echo "Echéance encaissée";
} catch (PDOExecption $e) {
	$db->rollBack();
	var_dump($e->getMessage());
}

?>
