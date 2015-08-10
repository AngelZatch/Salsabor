<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d');

try{
	$db->beginTransaction();
	$update = $db->prepare("UPDATE produits_echeances SET echeance_effectuee=2 WHERE date_echeance<=? AND echeance_effectuee=0");
	$update->bindParam(1, $compare_start);
	$update->execute();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>