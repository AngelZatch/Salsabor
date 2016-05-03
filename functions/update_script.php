<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
// Create the table participations
try{
	set_time_limit(0);
	$create = $db->query("CREATE TABLE participations(
passage_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
user_rfid VARCHAR(15) DEFAULT NULL,
room_token VARCHAR(20),
passage_date DATETIME,
user_id INT(11) DEFAULT NULL,
cours_id INT(11) DEFAULT NULL,
produit_adherent_id INT(11) DEFAULT NULL,
status TINYINT(4) COMMENT '0 : lecture de RFID / 1 : enregistrement pour association / 2 : passage validé / 3 : pas de forfait actif'
)");

	$duplicate = $db->query("CREATE TABLE old_cours_participants LIKE cours_participants");
	$duplicate = $db->query("INSERT old_cours_participants SELECT * FROM cours_participants");


	// Insert passages
	$insertPassages = $db->query("INSERT INTO participations(user_rfid, room_token, passage_date, user_id, cours_id, produit_adherent_id, status)
							SELECT passage_eleve, passage_salle, passage_date, passage_eleve_id, cours_id, produit_adherent_cible, status FROM passages");

	// Once passages have been inserted, we insert cours_participants
	$insertedLines = 0;
	$participations = $db->query("SELECT * FROM cours_participants cp
													LEFT JOIN cours c ON cp.cours_id_foreign = c.cours_id
													LEFT JOIN lecteurs_rfid lr ON c.cours_salle = lr.lecteur_ip");
	while($participation = $participations->fetch(PDO::FETCH_ASSOC)){
		$current_id = $participation["id"];
		$cours_id = $participation["cours_id_foreign"];
		$user_id = $participation["eleve_id_foreign"];
		$produit_adherent_id = $participation["produit_adherent_id"];
		if($participation["cours_start"] != null){
			$date_passage = $participation["cours_start"];
		} else {
			$date_passage = "";
		}
		if($participation["lecteur_ip"] != null){
			$room_token = $participation["lecteur_ip"];
		} else {
			$room_token = "";
		}
		$existant = $db->query("SELECT * FROM participations WHERE user_id = '$user_id' AND cours_id = '$cours_id'")->rowCount();
		if($existant == 0){
			$record = $db->query("INSERT INTO participations (room_token, passage_date, user_id, cours_id, produit_adherent_id, status)
												VALUES('$room_token', '$date_passage', '$user_id', '$cours_id', '$produit_adherent_id', '2')");
			$insertedLines++;
		} else {
			$record = $db->query("UPDATE participations SET produit_adherent_id = '$produit_adherent_id' WHERE user_id = '$user_id' AND cours_id = '$cours_id'");
		}
		$delete = $db->query("DELETE FROM cours_participants WHERE id = '$current_id'");
	}
	echo $insertedLines." lignes insérées";
} catch(PDOException $e){
	echo $e->getMessage();
}

?>
