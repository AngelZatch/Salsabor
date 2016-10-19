<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);

	$db->query("ALTER TABLE produits ADD COLUMN product_code VARCHAR(20) DEFAULT NULL AFTER product_name");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
