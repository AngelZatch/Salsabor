s<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d');

try{
	$db->beginTransaction();

	// Echeances non réglées dont la date est dépassée
	$update = $db->prepare("UPDATE produits_echeances SET echeance_effectuee=2 WHERE date_echeance<=? AND echeance_effectuee=0");
	$update->bindParam(1, $compare_start);
	$update->execute();

	// Désactivation des forfaits dont le volume horaire atteint 0
	$deactivateAbonnement = $db->prepare("UPDATE produits_adherents SET actif=0 WHERE date_expiration<=?");
	$deactivateAbonnement->bindParam(1, $compare_start);
	$deactivateAbonnement->execute();

	// Activation des forfaits à l'achat
	$activateProduit = $db->prepare("UPDATE produits SET actif=1 WHERE date_activation<=?");
	$activateProduit->bindParam(1, $compare_start);
	$activateProduit-> execute();

	// Désactivation des forfaits à l'achat
	$deactivateProduit = $db->prepare("UPDATE produits SET actif=0 WHERE date_desactivation<=?");
	$deactivateProduit->bindParam(1, $compare_start);
	$deactivateProduit->execute();

	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
