<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);
	// Delete the cours_suffixe from the cours table
	// derniere_modification : attributs ON UPDATE CURRENT_TIMESTAMP

	// Couple has to be unique
	$unique_assoc_session = $db->query("ALTER TABLE assoc_session_tags ADD UNIQUE(session_id_foreign, tag_id_foreign)");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
