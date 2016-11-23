<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);

	// Rename user_rib => rib
	// Set rib as VARCHAR(300)

	$db->query("CREATE TABLE logging(
	entry_id INT(11) AUTO_INCREMENT PRIMARY KEY,
	user_id INT(11),
	action VARCHAR(100),
	action_target VARCHAR(60),
	action_time DATETIME DEFAULT CURRENT_TIMESTAMP
	)");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
