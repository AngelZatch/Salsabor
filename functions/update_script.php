<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);

	$db->query("ALTER TABLE produits DROP COLUMN est_sans_engagement");
	$db->query("ALTER TABLE produits DROP COLUMN est_formation_professionnelle");
	$db->query("ALTER TABLE produits DROP COLUMN est_recharge");
	$db->query("ALTER TABLE produits DROP COLUMN est_illimite");
	$db->query("ALTER TABLE produits DROP COLUMN est_cours_particulier");
	$db->query("ALTER TABLE produits DROP COLUMN est_abonnement");
	$db->query("ALTER TABLE produits DROP COLUMN est_autre");
	$db->query("ALTER TABLE produits ADD COLUMN counts_holidays TINYINT(2) DEFAULT 1 AFTER product_validity");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
