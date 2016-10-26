<?php
require_once "/opt/lampp/htdocs/Salsabor/functions/db_connect.php";
require_once "/opt/lampp/htdocs/Salsabor/functions/compute_remaining_hours.php";
require_once "/opt/lampp/htdocs/Salsabor/functions/post_task.php";
require_once "/opt/lampp/htdocs/Salsabor/functions/attach_tag.php";
/*require_once "../db_connect.php";
require_once "../compute_remaining_hours.php";
require_once "../post_task.php";
require_once "../attach_tag.php";*/

$db = PDOFactory::getConnection();

/** This file just does the daily system_operations:
- Set products as expired
- Show/Hide promotions
- Watch for active/inactive users
- Clean obsolete notifications
It's executed once per day, at night because some operations (like computing all active products) might take some time.
cron line : cron : * 1 * * * /opt/lampp/bin/php /opt/lampp/htdocs/Salsabor/functions/schedule/system_operations.php
(will be executed daily at 1am)
**/

$compare_start = date("Y-m-d");
$activationLimit = date("Y-m-d H:i:s", strtotime($compare_start.'-1YEAR'));

try{
	$db->beginTransaction();

	// Compute all active products. As compute generates notifications, this will also take care of all the notifications for the products.
	$products = $db->query("SELECT id_produit_adherent FROM produits_adherents WHERE actif = 1");
	while($product = $products->fetch(PDO::FETCH_COLUMN)){
		computeProduct($product);
	}

	/*// Activate available promotions
	$toActivate = $db->query("SELECT product_id FROM produits WHERE date_activation <= '$compare_start' AND date_activation != '0000-00-00 00:00:00'");
	while($match = $toActivate->fetch(PDO::FETCH_ASSOC)){
		updateColumn($db, "produits", "actif", 1, $match["product_id"]);
		postNotification($db, "PRO-S", $match["product_id"], null, $compare_start);
	}

	// Or deactivate expired ones
	$toDeactive = $db->query("SELECT product_id FROM produits WHERE date_desactivation <= '$compare_start' AND date_desactivation != '0000-00-00 00:00:00'");
	while($match = $toDeactive->fetch(PDO::FETCH_ASSOC)){
		updateColumn($db, "produits", "actif", 0, $match["product_id"]);
		postNotification($db, "PRO-E", $match["product_id"], null, $compare_start);
	}*/

	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}

try{
	$db->beginTransaction();
	// We deactivate any user that didn't buy a product or attended a session for more than 12 months.
	$deactivateUser = $db->query("UPDATE users SET actif = 0 WHERE actif = '1' AND date_last < '$activationLimit'");
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}

try{
	$db->beginTransaction();
	// We check all active users whod don't have an active membership card
	$noCards = $db->query("SELECT user_id FROM users u WHERE actif = 1")->fetchAll(PDO::FETCH_COLUMN);
	foreach($noCards as $user){
		$test = $db->query("SELECT * FROM produits_adherents pa
							JOIN produits p ON pa.id_produit_foreign = p.product_id
							WHERE id_user_foreign = '$user' AND product_name = 'Adhésion Annuelle' AND pa.actif != 2")->rowCount();
		if($test == 0){
			$new_task_id = createTask($db, "Adhésion Annuelle manquante", "Cet utilisateur n'a pas d'adhésion annuelle.", "[USR-".$user."]", null);
			$tag = $db->query("SELECT rank_id FROM tags_user WHERE missing_info_default = 1")->fetch(PDO::FETCH_COLUMN);
			associateTag($db, intval($tag), $new_task_id, "task");
		}
	}
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}

// We delete "old" notifications about closed sessions
$delete_old_notifications = $db->query("DELETE FROM team_notifications WHERE notification_token = 'SES'");
?>
