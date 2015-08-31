<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$today = date_create('now')->format('Y-m-d H:i:s');

if(isset($_GET["carte"])){
	$data = explode('*', $_GET["carte"]);
	$tag_rfid = $data[0];
	$ip_rfid = $data[1];
	add($tag_rfid, $ip_rfid);
}

function add($tag, $ip){
	$db = PDOFactory::getConnection();
	$search = ("SELECT * FROM users JOIN produits_adherents ON user_id=produits_adherents.id_user_foreign WHERE user_rfid='$tag'");
	if($search->rowCount() == 0){
		$status = "1"; // Statut d'un nouveau code non associé
	} else {
		if($search["date_expiration"] <= $today){
			$status = "3";
		} else {
			$status = "0";
		}
	}

	$new = $db->prepare("INSERT INTO passages(passage_eleve, passage_salle, passage_date, status)
	VALUE(:tag, :salle, :date, :status)");
	$new->bindParam(':tag', $tag);
	$new->bindParam(':salle', $ip);
	$new->bindParam(':date', $today);
	$new->bindParam(':status', $status);
	$new->execute();

	echo $ligne = $today.";".$tag.";".$ip."$";
?>
