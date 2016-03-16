<?php
function getAdherent($prenom, $nom){
	$db = PDOFactory::getConnection();
	$search = $db->prepare('SELECT * FROM users WHERE user_prenom=? AND user_nom=?');
	$search->bindParam(1, $prenom);
	$search->bindParam(2, $nom);
	$search->execute();
	$res = $search->fetch(PDO::FETCH_ASSOC);
	return $res;
}

function solveAdherentToId($data){
	$db = PDOFactory::getConnection();
	$data = explode(' ', $data);
	$prenom = $data[0];
	$nom = '';
	for($i = 1; $i < count($data); $i++){
		$nom .= $data[$i];
		if($i != count($data)){
			$nom .= " ";
		}
	}
	$search = $db->prepare('SELECT * FROM users WHERE user_prenom=? AND user_nom=?');
	$search->bindParam(1, $prenom);
	$search->bindParam(2, $nom);
	$search->execute();
	$res = $search->fetch(PDO::FETCH_ASSOC);
	return $res["user_id"];
}

function getLieu($id){
	$db = PDOFactory::getConnection();
	$search = $db->prepare('SELECT * FROM salle WHERE salle_id=?');
	$search->bindParam(1, $id);
	$search->execute();
	$res = $search->fetch(PDO::FETCH_ASSOC);
	return $res;
}

function generateReference() {
	$length = 10;
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$chars_length = strlen($characters);
	$reference = '';
	for ($i = 0; $i < $length; $i++) {
		$reference .= $characters[rand(0, $chars_length - 1)];
	}
	return $reference;
}

function computeExpirationDate($db, $date_activation, $validity){
	$date_expiration = date("Y-m-d 00:00:00", strtotime($date_activation.'+'.$validity.'DAYS'));
	$queryHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee >= ? AND date_chomee <= ?");
	$queryHoliday->bindParam(1, $date_activation);
	$queryHoliday->bindParam(2, $date_expiration);
	$queryHoliday->execute();

	$j = 0;

	for($i = 0; $i <= $queryHoliday->rowCount(); $i++){
		$exp_date = date("Y-m-d 00:00:00",strtotime($date_expiration.'+'.$i.'DAYS'));
		$checkHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee=?");
		$checkHoliday->bindParam(1, $exp_date);
		$checkHoliday->execute();
		if($checkHoliday->rowCount() != 0){
			$j++;
		}
		$totalOffset = $i + $j;
		$new_exp_date = date("Y-m-d 00:00:00",strtotime($date_expiration.'+'.$totalOffset.'DAYS'));
	}
	if(!isset($new_exp_date)){
		$new_exp_date = $date_expiration;
	}
	return $new_exp_date;
}
?>
