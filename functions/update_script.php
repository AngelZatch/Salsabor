<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);
	// Delete the cours_suffixe from the cours table
	// derniere_modification : attributs ON UPDATE CURRENT_TIMESTAMP

	// Couple has to be unique
	$unique_assoc_session = $db->query("ALTER TABLE assoc_session_tags ADD UNIQUE(session_id_foreign, tag_id_foreign)");
	$unique_assoc_session = $db->query("ALTER TABLE assoc_page_tags ADD UNIQUE(page_id_foreign, tag_id_foreign)");
	$unique_assoc_session = $db->query("ALTER TABLE assoc_task_tags ADD UNIQUE(task_id_foreign, tag_id_foreign)");
	$unique_assoc_session = $db->query("ALTER TABLE assoc_user_tags ADD UNIQUE(user_id_foreign, tag_id_foreign)");
	$unique_assoc_session = $db->query("ALTER TABLE tags_session ADD UNIQUE(rank_name)");
	$unique_assoc_session = $db->query("ALTER TABLE tags_user ADD UNIQUE(rank_name)");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
