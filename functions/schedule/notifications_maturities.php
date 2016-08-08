<?php
/** This file has to be executed daily and will create notifications for the team.

cron line : 0 10 * * * php -f /opt/lampp/htdocs/Salsabor/functions/schedule/notifications_maturities.php
(will be executed at 10am every day)

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
require_once "/opt/lampp/htdocs/Salsabor/functions/db_connect.php";
include "/opt/lampp/htdocs/Salsabor/functions/tools.php";
/*require_once "../db_connect.php";
include "../tools.php";*/
$db = PDOFactory::getConnection();

$master_settings = $db->query("SELECT * FROM master_settings WHERE user_id = 0")->fetch(PDO::FETCH_ASSOC);

$today = date("Y-m-d H:i:s");
$expiration_limit = date("Y-m-d", strtotime($today.'+'.$master_settings["days_before_exp"].'DAYS'));
$maturity_limit = date("Y-m-d", strtotime($today.'+'.$master_settings["days_before_maturity"].'DAYS'));
$maturity_over = date("Y-m-d", strtotime($today.'-'.$master_settings["days_after_maturity"].'DAYS'));
$hour_limit = $master_settings["hours_before_exp"];

/*
MATURITIES
For maturities, we will take all maturities which are still not paid and are coming in the next z days or have passed their date since z' days.
*/
$maturities = $db->query("SELECT * FROM produits_echeances
						WHERE date_paiement IS NULL
						AND (date_echeance <= '$maturity_limit' OR date_echeance >= '$maturity_over')");
while($maturity = $maturities->fetch(PDO::FETCH_ASSOC)){
	$token = "MAT-";
	if($maturity["date_echeance"] <= $maturity_limit){
		$token .= "NE";
	} else if($maturity["date_echeance"] >= $maturity_over){
		$token .= "L";
	}
	$target = $maturity["produits_echeances_id"];
	$date = date("Y-m-d H:i:s");

	postNotification($db, $token, $target, null, $date);
}


?>
