<?php
require_once "/opt/lampp/htdocs/Salsabor/functions/db_connect.php";
require_once "/opt/lampp/htdocs/Salsabor/functions/tools.php";
include "/opt/lampp/htdocs/Salsabor/functions/compute_remaining_hours.php";
/*require_once "../db_connect.php";
require_once "../compute_remaining_hours.php";*/

$db = PDOFactory::getConnection();

/** This file just does the daily system_operations:
- Set maturities as late
- Set products as expired
- Show/Hide promotions
- Watch for active/inactive users
It's executed once per day, at night because some operations (like computing all active products) might take some time.
cron line : cron : * 1 * * * php -f /opt/lampp/htdocs/Salsabor/functions/schedule/system_operations.php
(will be executed daily at 1am)
**/

$compare_start = date("Y-m-d");
$activationLimit = date("Y-m-d H:i:s", strtotime($compare_start.'-1YEAR'));

try{
	$db->beginTransaction();

	// Late maturities
	$update = $db->prepare("UPDATE produits_echeances SET echeance_effectuee=2 WHERE date_echeance<=? AND echeance_effectuee=0");
	$update->bindParam(1, $compare_start);
	$update->execute();

	// Compute all active products. As compute generates notifications, this will also take care of all the notifications for the products.
	$products = $db->query("SELECT id_produit_adherent FROM produits_adherents WHERE actif = 1");
	while($product = $products->fetch(PDO::FETCH_COLUMN)){
		computeProduct($product);
	}

	// Activate available promotions
	$toActivate = $db->query("SELECT produit_id FROM produits WHERE date_activation <= '$compare_start'");
	while($match = $toActivate->fetch(PDO::FETCH_ASSOC)){
		updateColumn($db, "produits", "actif", 1, $match["produit_id"]);
		postNotification($db, "PRO-S", $match["produit_id"], null, $compare_start);
	}

	// Or deactivate expired ones
	$toDeactive = $db->query("SELECT produit_id FROM produits WHERE date_desactivation <= '$compare_start'");
	while($match = $toDeactive->fetch(PDO::FETCH_ASSOC)){
		updateColumn($db, "produits", "actif", 0, $match["produit_id"]);
		postNotification($db, "PRO-E", $match["produit_id"], null, $compare_start);
	}

	$findActive = $db->query("SELECT date_achat, payeur_transaction FROM transactions GROUP BY payeur_transaction");

	// We deactivate any user that didn't buy a product or attended a session for more than 12 months.
	$deactivateUser = $db->query("UPDATE users SET actif = 0 WHERE actif = '1' AND date_last < '$activationLimit'");

	// We delete "old" notifications about closed sessions
	$delete_old_notifications = $db->query("DELETE FROM team_notifications WHERE notification_token = 'SES'");

	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
