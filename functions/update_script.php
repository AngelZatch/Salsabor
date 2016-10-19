<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);

	$db->query("ALTER TABLE produits ADD COLUMN product_code VARCHAR(20) DEFAULT NULL AFTER product_name");

	$db->query("CREATE TABLE product_categories(
	category_id INT(11) AUTO_INCREMENT PRIMARY KEY,
	category_name VARCHAR(80)
	)");

	$db->query("ALTER TABLE produits
	ADD product_category INT(11) DEFAULT NULL AFTER product_code,
	ADD CONSTRAINT fk_product_category FOREIGN KEY(product_category)
	REFERENCES product_categories(category_id)
	ON DELETE SET NULL
	ON UPDATE NO ACTION");

	$db->query("INSERT INTO app_pages(page_name, page_glyph, page_url, page_menu, page_order)
			VALUES('Catégories', 'th-list', 'categories-produits', 3, 5)");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
