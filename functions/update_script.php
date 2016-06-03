<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
try{
	set_time_limit(0);
	// Create the locations table
	$locations = $db->query("CREATE TABLE locations(
	location_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	location_name VARCHAR(50),
	location_address VARCHAR(200)
	)");
	// Create the table rooms that will replace "salles"
	$rooms = $db->query("CREATE TABLE rooms(
	room_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	room_location INT(11),
	room_name VARCHAR(40)
	)");
	// Foreign key : room_location -> locations.location_id ON DELETE CASCADE ON UPDATE NO ACTION
	// Create table readers that will replace "lecteurs_rfid"
	$readers = $db->query("CREATE TABLE readers(
	reader_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	reader_token VARCHAR(25),
	reader_room INT(11) DEFAULT NULL
	)");
	// Foreign key : reader_room -> rooms.room_id ON DELETE SET NULL ON UPDATE NO ACTION
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
