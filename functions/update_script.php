<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);
	// October 11th
	$db->query("INSERT INTO app_pages(page_name, page_glyph, page_url, page_menu, page_order)
					VALUES('Doublons', 'duplicate', 'doublons', 5, 3)");

	$db->query("CREATE TABLE teacher_rates(
		rate_id INT(11) AUTO_INCREMENT PRIMARY KEY,
		user_id_foreign INT(11),
		rate_title VARCHAR(70),
		rate_value DECIMAL(11,2),
		rate_ratio SET('heure','prestation','personne'))");

	$db->query("ALTER TABLE sessions
		ADD teacher_rate INT(11) AFTER session_teacher,
		ADD CONSTRAINT fk_teacher_rate FOREIGN KEY (teacher_rate)
		REFERENCES teacher_rates(rate_id)
		ON DELETE SET NULL
		ON UPDATE NO ACTION");

	$db->query("ALTER TABLE transactions
		ADD transaction_handler INT(11) AFTER date_achat,
		ADD CONSTRAINT fk_transaction_handler FOREIGN KEY (transaction_handler)
		REFERENCES users(user_id)
		ON DELETE SET NULL
		ON UPDATE NO ACTION");

	$db->query("ALTER TABLE users
		ADD website VARCHAR(300) DEFAULT NULL AFTER mail,
		ADD organisation VARCHAR(200) DEFAULT NULL AFTER website,
		ADD archived TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0 : non / 1 : oui (archivé)'");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
