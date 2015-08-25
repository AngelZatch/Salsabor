<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id_produit = $_POST["produit_id"];

try{
	$db->beginTransaction();
	$new = $db->prepare('INSERT INTO panier(panier_element) VALUES(:id_produit)');
	$new->bindParam(':id_produit', $id_produit);
	$new->execute();
	$db->commit();
	echo "Produit ajouté au panier";
} catch(PDOException $e){
	$db->rollBack();
}
?>