<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();
require_once "tools.php";

$cours_id = $_POST["cours_id"];

$data = explode(' ', $_POST["adherent"]);
$prenom = $data[0];
$nom = '';
for($i = 1; $i < count($data); $i++){
	$nom .= $data[$i];
	if($i != count($data)){
		$nom .= " ";
	}
}
$adherent = getAdherent($prenom, $nom);

$cours = $db->query("SELECT cours_id, cours_start, cours_salle, lecteur_ip FROM cours
					JOIN lecteurs_rfid ON cours_salle=lecteurs_rfid.lecteur_lieu
					WHERE cours_id=$cours_id")->fetch(PDO::FETCH_ASSOC);

$search = $db->query("SELECT * FROM users
					JOIN produits_adherents ON user_id=produits_adherents.id_user_foreign
					WHERE user_rfid='$adherent[user_rfid]'");
$res = $search->fetch(PDO::FETCH_ASSOC);
if($search->rowCount() == 0 || $res["date_expiration"] <= $cours["cours_start"]){
	$status = "3";
} else {
	$status = "0";
}

$passage_rfid = $adherent["user_rfid"];
$passage_eleve_id = $adherent["user_id"];

try{
	$db->beginTransaction();
	$new = $db->prepare('INSERT INTO passages(passage_eleve, passage_eleve_id, passage_salle, passage_date, cours_id, status)
	VALUES(:rfid, :user_id, :salle, :date, :cours_id, :status)');
	$new->bindParam(':rfid', $passage_rfid);
	$new->bindParam(':user_id', $passage_eleve_id);
	$new->bindParam(':salle', $cours["lecteur_ip"]);
	$new->bindParam(':date', $cours["cours_start"]);
	$new->bindParam(':cours_id', $cours["cours_id"]);
	$new->bindParam(':status', $status);
	$new->execute();
	$db->commit();
	echo "Passage enregistré.";
} catch(PDOException $e){
	$db->rollBack();
	var_dump($e->getMessage());
}
?>
