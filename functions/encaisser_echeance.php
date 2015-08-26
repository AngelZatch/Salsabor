<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["echeance_id"];

try{
	$db->beginTransaction();
	$update = $db->prepare("UPDATE produits_echeances SET statut_banque=1 WHERE id_echeance=?");
	$update->bindParam(1, $id);
	$update->execute();
	$db->commit();
	echo "Echéance encaissée";
} catch (PDOExecption $e) {
	$db->rollBack();
	var_dump($e->getMessage());
}

?>
