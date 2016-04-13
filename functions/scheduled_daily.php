<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d');
$activationLimit = date("Y-m-d H:i:s", strtotime($compare_start.'-1YEAR'));

try{
	$db->beginTransaction();

	// Echeances non réglées dont la date est dépassée
	$update = $db->prepare("UPDATE produits_echeances SET echeance_effectuee=2 WHERE date_echeance<=? AND echeance_effectuee=0");
	$update->bindParam(1, $compare_start);
	$update->execute();

	// Désactivation des forfaits dont la date d'expiration est dépassée
	$deactivateAbonnement = $db->prepare("UPDATE produits_adherents SET actif=2 WHERE date_expiration<=?");
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

	$findActive = $db->query("SELECT date_achat, payeur_transaction FROM transactions GROUP BY payeur_transaction");

	// We deactivate any user that didn't buy a product or attended a session for more than 3 months.
	$deactivateUser = $db->query("UPDATE users SET actif = 0 WHERE actif = '1' AND date_last < '$activationLimit'");

	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
