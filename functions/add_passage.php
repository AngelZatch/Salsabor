<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();
include "tools.php";

$data = explode(' ', $_POST["adherent"]);
$prenom = $data[0];
$nom = $data[1];

$adherent = getAdherent($prenom, $nom);
$date = date_create('now')->format('Y-m-d H:i:s');

$salle = $db->query("SELECT * FROM cours JOIN lecteurs_rfid ON cours_salle = lecteurs_rfid.lecteur_lieu WHERE cours_id=$_POST[cours_id]")->fetch(PDO::FETCH_ASSOC);

$search = $db->query("SELECT * FROM users JOIN produits_adherents ON user_id=produits_adherents.id_user_foreign WHERE user_rfid='$adherent[user_rfid]'");
$res = $search->fetch(PDO::FETCH_ASSOC);
if($search->rowCount() == 0 || $res["date_expiration"] <= $date){
	$status = "3";
} else {
	$status = "0";
}

try{
	$db->beginTransaction();
	$new = $db->prepare('INSERT INTO passages(passage_eleve, passage_salle, passage_date, cours_id, status)
	VALUES(:user_id, :salle, :date, :cours_id, :status)');
	$new->bindParam(':user_id', $adherent["user_rfid"]);
	$new->bindParam(':salle', $salle["lecteur_ip"]);
	$new->bindParam(':date', $date);
	$new->bindParam(':cours_id', $_POST["cours_id"]);
	$new->bindParam(':status', $status);
	$new->execute();
	$db->commit();
	echo "Passage enregistré.";
} catch(PDOException $e){
	$db->rollBack();
	$message = var_dump($e->getMessage());
	$data = array('type' => 'error', 'message' => ' '.$message);
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
}
?>