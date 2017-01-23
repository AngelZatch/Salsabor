<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);

	// Create billing table for teachers
	// The table will keep track of every invoice made to pay people. It has to contain the user who will get paid, the total price and what's being paid. It'll also contain two status and their flags : emitted and paid.

	/*$db->query("CREATE TABLE invoices (
	invoice_id INT(11) AUTO_INCREMENT PRIMARY KEY,
	invoice_token VARCHAR(20),
	invoice_seller_id INT(11) DEFAULT NULL,
	invoice_period DATETIME DEFAULT NULL,
	invoice_price DOUBLE(11,2),
	invoice_reception_date DATETIME DEFAULT NULL,
	invoice_payment_date DATETIME DEFAULT NULL,
	invoice_file VARCHAR(200) DEFAULT NULL
	)");

	$db->query("ALTER TABLE invoices
	ADD CONSTRAINT fk_seller_id FOREIGN KEY(invoice_seller_id)
	REFERENCES users(user_id)
	ON DELETE SET NULL
	ON UPDATE NO ACTION");

	// Since sessions are contained in only one bill, a new field referencing the invoices table is added to the session table
	$db->query("ALTER TABLE sessions
	ADD COLUMN invoice_id INT(11) DEFAULT NULL AFTER session_price,
	ADD CONSTRAINT fk_invoice_id FOREIGN KEY(invoice_id)
	REFERENCES invoices(invoice_id)
	ON DELETE SET NULL
	ON UPDATE NO ACTION");

	// Renaming useless table prestations to prestation_type
	$db->query("RENAME TABLE prestations TO prestation_types");

	// Create the prestations table
	$db->query("CREATE TABLE prestations (
	prestation_id INT(11) AUTO_INCREMENT PRIMARY KEY,
	prestation_start DATETIME DEFAULT NULL,
	prestation_end DATETIME DEFAULT NULL,
	prestation_address TEXT DEFAULT NULL,
	prestation_handler INT(11) DEFAULT NULL,
	prestation_description TEXT DEFAULT NULL,
	prestation_price DOUBLE(11,2) DEFAULT NULL,
	invoice_id INT(11) DEFAULT NULL
	)");

	$db->query("ALTER TABLE prestations
	ADD CONSTRAINT fk_handler_id FOREIGN KEY(prestation_handler)
	REFERENCES users(user_id)
	ON DELETE SET NULL
	ON UPDATE NO ACTION");

	$db->query("ALTER TABLE prestations
	ADD CONSTRAINT fk_invoice_id FOREIGN KEY(invoice_id)
	REFERENCES invoices(invoice_id)
	ON DELETE SET NULL
	ON UPDATE NO ACTION");

	$db->query("CREATE TABLE prestation_users(
	prestation_id INT (11),
	user_id INT (11),
	invoice_id INT (11) DEFAULT NULL,
	price DOUBLE (11,2) DEFAULT NULL)");*/

	$db->query("ALTER TABLE prestation_users
	ADD CONSTRAINT fk_prestation_id FOREIGN KEY(prestation_id)
	REFERENCES prestations(prestation_id)
	ON DELETE CASCADE
	ON UPDATE RESTRICT");

	$db->query("ALTER TABLE prestation_users
	ADD CONSTRAINT fk_user_id FOREIGN KEY(user_id)
	REFERENCES users(user_id)
	ON DELETE CASCADE
	ON UPDATE RESTRICT");

	$db->query("ALTER TABLE prestation_users
	ADD CONSTRAINT fk_invoice_id FOREIGN KEY(invoice_id)
	REFERENCES invoices(invoice_id)
	ON DELETE SET NULL
	ON UPDATE NO ACTION");

} catch(PDOException $e){
	echo $e->getMessage();
}
?>
