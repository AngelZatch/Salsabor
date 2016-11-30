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

	$db->query("ALTER TABLE reservations
	ADD booking_handler INT(11) DEFAULT NULL AFTER booking_price,
	ADD CONSTRAINT fk_booking_handler FOREIGN KEY(booking_handler)
	REFERENCES users(user_id)
	ON DELETE SET NULL
	ON UPDATE NO ACTION");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
