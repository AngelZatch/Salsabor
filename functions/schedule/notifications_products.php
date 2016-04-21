<?php
/** This file has to be executed very often and will create notifications for the team.

cron line : * /10 11-23 * * * php -f /opt/lampp/htdocs/Salsabor/functions/schedule/notifications_maturities.php
(will be executed every 10 minutes between 11am and 11pm everyday)

Notifications can target a transaction, a maturity, a mail
A notification will have token which will be read by the application to know what it's about:
-> Type TRA (Transaction), PRD (Product), MAT (Maturity), MAI (Mail)
-> Subtype NE (Near Expiration), NH (Near Hour Limit), E (Expired), L (Late)
-> Target the ID of the transaction, maturity, product, user...
-> Date time of the notification
-> State 0 (read), 1 (new)

exemple: a product is gonna expire in 2 days:
PRD-NE | 1275 | 1 - The product PRODUCT_NAME of USER will expire on PRODCUT_VALIDITY.
PRD-NH | 1275 | 1 - The product PRODUCT_NAME of USER has HOUR remaining.
MAT-L | 10024 | 1 - "The Maturity of the transaction TRANSACTION_ID of user USER, scheduled for MATURITY_DATE, has not been paid yet.
**/
require_once "../db_connect.php";
$db = PDOFactory::getConnection();

$master_settings = $db->query("SELECT * FROM master_settings WHERE user_id = 0")->fetch(PDO::FETCH_ASSOC);

$today = date("Y-m-d H:i:s");
$expiration_limit = date("Y-m-d", strtotime($today.'+'.$master_settings["days_before_exp"].'DAYS'));
$maturity_limit = date("Y-m-d", strtotime($today.'+'.$master_settings["days_before_maturity"].'DAYS'));
$maturity_over = date("Y-m-d", strtotime($today.'-'.$master_settings["days_after_maturity"].'DAYS'));
$hour_limit = $master_settings["hours_before_exp"];

/*
PRODUCTS
For products, we will take all the products that will expire in the next x days, and all the products that have less than y hours remaining but have initially more than y hours (so things like afterworks, unlimited and 1 hour products won't be accounted; we don't care about these).
*/
$products = $db->query("SELECT * FROM produits_adherents pa
						JOIN produits p ON pa.id_produit_foreign = p.produit_id
						WHERE pa.actif = 1 AND validite_initiale > '$hour_limit'
						AND ((date_expiration <= '$expiration_limit' OR (date_prolongee != '0000-00-00 00:00:00' AND date_prolongee <= '$expiration_limit')) OR (volume_cours > 0 AND volume_cours <= '$hour_limit' AND est_abonnement = 0))");

while($product = $products->fetch(PDO::FETCH_ASSOC)){
	$token = "PRD-";
	$target = $product["id_produit_adherent"];
	$date = $today;
	$exp_date = max($product["date_expiration"], $product["date_prolongee"]);
	if($exp_date <= $expiration_limit){
		$token .= "NE";
		$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_date, notification_state)
								VALUES('$token', '$target', '$date', '1')");
	} else if($product["volume_cours"] > 0 && $product["volume_cours"] <= $hour_limit){
		$token .= "NH";
		$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_date, notification_state)
								VALUES('$token', '$target', '$date', '1')");
	}
}
?>
