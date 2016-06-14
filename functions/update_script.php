<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);
	// Delete the cours_suffixe from the cours table
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
