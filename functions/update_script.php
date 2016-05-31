<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);
	// Create the tasks table
	$tasks = $db->query("CREATE TABLE tasks(
	task_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	task_token VARCHAR(7),
	task_target VARCHAR(15),
	task_recipient INT(11) DEFAULT NULL COMMENT 'Destinataire de la tâche',
	task_title VARCHAR(80),
	task_description TEXT,
	task_creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
	task_last_update DATETIME,
	task_deadline DATETIME,
	task_state TINYINT(4) DEFAULT '0' COMMENT '0 : not done / 1 : done'
	)");
	// Create the task_comments table
	$task_comments = $db->query("CREATE TABLE task_comments(
	task_comment_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	task_id_foreign INT(11),
	task_comment TEXT,
	task_comment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
	task_comment_author INT(11)
	)");
	// Rename the rank table to tags_user
	$tags_user = $db->query("RENAME TABLE rank TO tags_user");
	// Add the color field
	$tags_user = $db->query("ALTER TABLE tags_user
	ADD tag_color VARCHAR(6) DEFAULT 'a80139'");
	// Create the colors table
	$colors = $db->query("CREATE TABLE colors(
	color_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	color_value VARCHAR(6)
	)");
	// Insert into the newfound table the values
	$colors = $db->query("INSERT INTO colors(color_value)
						VALUES('e416a1'), ('e416a1'), ('04c9b8'), ('31a03f'), ('fb4836'), ('a16ce8'), ('c9c00c'), ('0954ee'), ('ca2004'), ('a80139'), ('ff8f00')");
	// Create the assoc_user_tags table
	$assoc_user_tags = $db->query("CREATE TABLE assoc_user_tags(
	entry_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id_foreign INT(11),
	tag_id_foreign INT(11)
	)");
	// When migrating, we have to foreign key tag_id_foreign to tags_user.rank_id ON DELETE CASCADE
	// Create the assoc_task_tags table
	$assoc_task_tags = $db->query("CREATE TABLE assoc_task_tags(
	entry_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	task_id_foreign INT(11),
	tag_id_foreign INT(11)
	)");
	// Same as for the previous table, foreign key tag_id_foreign to tags_user.rank_id ON DELETE CASCADE
	// Delete all the now useless fields from the user table
	$users = $db->query("ALTER TABLE users
						DROP est_membre,
						DROP est_staff,
						DROP est_professeur,
						DROP est_prestataire,
						DROP est_autre,
						DROP autre_statut");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
