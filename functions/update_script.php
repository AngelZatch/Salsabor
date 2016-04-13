<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

/** Script to call after a system update when a database update is required **/
$update = $db->query("ALTER TABLE users
					CHANGE date_inactivation date_last DATETIME
					ALTER actif DEFAULT '1'");

/** Now we have to see all active users **/
$findActive = $db->query("SELECT date_achat, payeur_transaction FROM transactions GROUP BY payeur_transaction");

while($active = $findActive->fetch(PDO::FETCH_ASSOC)){
	$lastDate = $active["date_achat"];
	$user_id = $active["payeur_transaction"];
	$activateUser = $db->query("UPDATE users SET actif = '1', date_last='$lastDate' WHERE user_d='$user_id'");
}
?>
