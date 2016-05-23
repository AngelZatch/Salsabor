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

function solveAdherentToId($name){
	$db = PDOFactory::getConnection();
	$user = $db->query("SELECT * FROM (
	SELECT user_id, CONCAT(user_prenom, ' ', user_nom) as fullname FROM users) base
	WHERE fullname = '$name'");
	$res = $user->fetch(PDO::FETCH_ASSOC);
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
	$validity--;
	$date_expiration = date("Y-m-d 23:59:59", strtotime($date_activation.'+'.$validity.'DAYS'));
	$queryHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee >= ? AND date_chomee <= ?");
	$queryHoliday->bindParam(1, $date_activation);
	$queryHoliday->bindParam(2, $date_expiration);
	$queryHoliday->execute();

	$j = 0;

	for($i = 0; $i < $queryHoliday->rowCount(); $i++){
		$exp_date = date("Y-m-d 23:59:59",strtotime($date_expiration.'+'.$i.'DAYS'));
		$checkHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee=?");
		$checkHoliday->bindParam(1, $exp_date);
		$checkHoliday->execute();
		if($checkHoliday->rowCount() != 0){
			$j++;
		}
		$totalOffset = $i + $j;
		$new_exp_date = date("Y-m-d 23:59:59",strtotime($date_expiration.'+'.$totalOffset.'DAYS'));
	}
	if(!isset($new_exp_date)){
		$new_exp_date = $date_expiration;
	}
	return $new_exp_date;
}

function addParticipation($db, $cours_name, $session_id, $user_id, $ip, $tag){
	$today = date_create('now')->format('Y-m-d H:i:s');
	if($session_id != null){ // If we could find a session, then we're gonna look for a product.
		if(preg_match("/jazz/i", $cours_name, $matches) || preg_match("/pilates/i", $cours_name, $matches) || preg_match("/particulier/i", $cours_name, $matches)){ // Search for specific Jazz, Pilates or private sessions
			/*echo $matches[0];*/
			$checkSpecific = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom LIKE '%$matches[0]%'
									AND pa.actif != '2'
									ORDER BY date_achat ASC");
			if($checkSpecific->rowCount() > 0){
				$product = $checkSpecific->fetch(PDO::FETCH_ASSOC);
			}
		} else { // First, we search for any freebies
			$checkInvitation = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom = 'Invitation'
									AND pa.actif = '0'
									ORDER BY date_achat ASC");
			if($checkInvitation->rowCount() > 0){ // If there are freebies still available, we take the first one.
				$product = $checkInvitation->fetch(PDO::FETCH_ASSOC);
			} else { // If no freebies, we look for every currently active products.
				$checkActive = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom != 'Invitation'
									AND produit_nom NOT LIKE '%jazz%'
									AND produit_nom NOT LIKE '%pilates%'
									AND produit_nom NOT LIKE '%particulier%'
									AND pa.actif = '1'
									AND est_abonnement = '0'
									AND est_cours_particulier = '0'
									ORDER BY date_achat ASC");
				if($checkActive->rowCount() > 0){ // If there are active products that are not an annual sub
					$product = $checkActive->fetch(PDO::FETCH_ASSOC);
				} else { // We check inactive products now.
					$checkPending = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom != 'Invitation'
									AND produit_nom NOT LIKE '%jazz%'
									AND produit_nom NOT LIKE '%pilates%'
									AND produit_nom NOT LIKE '%particulier%'
									AND pa.actif = '0'
									AND est_abonnement = '0'
									AND est_cours_particulier = '0'
									ORDER BY date_achat ASC");
					if($checkPending->rowCount() > 0){
						$product = $checkPending->fetch(PDO::FETCH_ASSOC);
					}
				}
			}
		}
		if(isset($product)){
			$product_id = $product["id_produit_adherent"];
			$status = "0";
		} else {
			$product = NULL;
			$status = "3";
		}
		$new = $db->query("INSERT INTO participations(user_rfid, user_id, room_token, passage_date, cours_id, produit_adherent_id, status)
					VALUES('$tag', '$user_id', '$ip', '$today', '$session_id', '$product_id', '$status')");
	} else {
		$status = "2";
		$new = $db->query("INSERT INTO participations(user_rfid, user_id, room_token, passage_date, status)
					VALUES('$tag', '$user_id', '$ip', '$today', '$status')");
	}
	echo $ligne = $today.";".$tag.";".$ip."$-".$status;
}

function postNotification($db, $token, $target, $date){
	$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_date, notification_state)
								VALUES('$token', '$target', '$date', '1')");
}

function updateColumn($db, $table, $column, $value, $target_id){
	$now = date("Y-m-d H:i:s");
	$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
	if($column == "task_recipient"){
		if($value != ""){
			$value = solveAdherentToId($value);
		} else {
			$value = null;
		}
	}
	try{
		$primary_key = $db->query("SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'")->fetch(PDO::FETCH_ASSOC);

		$query = "UPDATE $table SET $column = '$value'";

		if($table == "tasks"){
			$query .= ", task_last_update = '$now'";
		}

		$query .= " WHERE $primary_key[Column_name] = '$target_id'";

		$update = $db->query($query);
	} catch(PDOException $e){
		echo $e->getMessage();
	}
}
?>
