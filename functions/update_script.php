<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);
	// Add menu_order to the app_menus table
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
