<?php
function solveAdherentToId($name){
	$db = PDOFactory::getConnection();
	$stmt = $db->prepare("SELECT * FROM (
							SELECT user_id, CONCAT(user_prenom, ' ', user_nom) as fullname FROM users) base
						WHERE fullname = ?");
	$stmt->bindParam(1, htmlspecialchars($name), PDO::PARAM_STR);
	$stmt->execute();
	$res = $stmt->fetch(PDO::FETCH_ASSOC);
	return $res["user_id"];
}

function getLieu($id){
	$db = PDOFactory::getConnection();
	$stmt = $db->prepare('SELECT * FROM rooms WHERE room_id=?');
	$stmt->bindParam(1, $id, PDO::PARAM_INT);
	$stmt->execute();
	$res = $stmt->fetch(PDO::FETCH_ASSOC);
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

function computeExpirationDate($db, $date_activation, $validity, $has_holidays){
	$validity--;
	$date_expiration = date("Y-m-d 23:59:59", strtotime($date_activation.'+'.$validity.'DAYS'));
	if($has_holidays){
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
		echo "$";
	} else {
		$status = "4";
		$new = $db->query("INSERT INTO participations(user_rfid, user_id, room_token, passage_date, status)
					VALUES('$tag', '$user_id', '$ip', '$today', '$status')");
		echo $status;
	}
	// If the user doesn't have any mail address
	$stmt = $db->prepare("SELECT mail FROM users WHERE user_id = ?");
	$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
	$stmt->execute();
	$mail = $stmt->fetch(PDO::FETCH_COLUMN);
	if($mail == ""){
		include 'post_task.php';
		include 'attach_tag.php';
		$new_task_id = createTask($db, "Manque d'informations pour !USER!", "Aucune adresse mail n'a été détectée pour cet utilisateur. Cette tâche a été créée car l'utilisateur est actuellement présent en cours.", "[USR-".$user_id."]");
		// Tag can now change because it's set by the team.
		$tag = $db->query("SELECT rank_id FROM tags_user WHERE missing_info_default = 1")->fetch(PDO::FETCH_COLUMN);
		associateTag($db, intval($tag), $new_task_id, "task");
	}
}

function addParticipationBeta($db, $today, $session_id, $user_id, $reader_token, $user_tag){
	if($user_id != ""){
		if($session_id != ""){
			$product_id = getCorrectProductFromTags($db, $session_id, $user_id);
			if($product_id != "")
				$status = 0;
			else
				$status = 3;
			$new = $db->query("INSERT INTO participations(user_rfid, user_id, room_token, passage_date, cours_id, produit_adherent_id, status)
						VALUES('$user_tag', '$user_id', '$reader_token', '$today', '$session_id', '$product_id', $status)");
			echo "$";
		} else {
			$status = 4;
			$new = $db->query("INSERT INTO participations(user_rfid, user_id, room_token, passage_date, status)
						VALUES('$user_tag', '$user_id', '$reader_token', '$today', $status)");
			echo "$";
		}
	} else {
		$status = 5;
		$new = $db->query("INSERT INTO participations(user_rfid, room_token, passage_date, status)
						VALUES('$user_tag', '$reader_token', '$today', '$status')");
		echo "$";
	}
}

function getCorrectProductFromTags($db, $session_id, $user_id){
	/** When a participation is recorded, this function will be called to find the correct product of the user based on the tags of the session the user is attending to **/
	$tags_session = $db->query("SELECT tag_id_foreign, is_mandatory FROM assoc_session_tags ast
								JOIN tags_session ts ON ts.rank_id = ast.tag_id_foreign
								WHERE session_id_foreign = $session_id
								ORDER BY is_mandatory DESC")->fetchAll(PDO::FETCH_ASSOC);

	// Step one : cross array with all mandatory tags to get only subs that fit the mandatory tags.
	// Create an array that takes all the mandatory_result arrays and intersect then later.
	$mandatory_arrays = [];
	$i = 0;
	foreach($tags_session as $tag){
		if($tag["is_mandatory"] == 1){
			$query = "SELECT id_produit_foreign FROM produits_adherents pa
				LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
				LEFT JOIN assoc_product_tags apt ON p.produit_id = apt.product_id_foreign
				LEFT JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
				WHERE tag_id_foreign = $tag[tag_id_foreign]
				AND id_user_foreign = $user_id
				AND pa.actif != 2
				ORDER BY date_achat DESC";
			$mandatory_arrays[$i] = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);
		}
		$i++;
	}
	echo "result<br>";
	if(sizeof($mandatory_arrays) > 1)
		$result = call_user_func_array("array_intersect", $mandatory_arrays);
	else
		$result = $mandatory_arrays[0];

	// Step two : take the product_id with the highest number of fitting tags.
	if(sizeof($result) == 1){ // If there's only one product that can fit.
		$query = "SELECT id_produit_adherent FROM produits_adherents pa
				WHERE id_produit_foreign = $result[0]
				AND id_user_foreign = $user_id";
		$product_id = $db->query($query)->fetch(PDO::FETCH_COLUMN);
		return $product_id;
	} else if(sizeof($result) > 1){ // If there are more than 1 product fitting, we test non-mandatory tags
		$supplementary_arrays = [];
		$i = 0;
		foreach($tags_session as $tag){
			if($tag["is_mandatory"] == 0){
				$query = "SELECT id_produit_adherent FROM produits_adherents pa
				LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
				LEFT JOIN assoc_product_tags apt ON p.produit_id = apt.product_id_foreign
				WHERE tag_id_foreign = $tag[tag_id_foreign]";
				if(sizeof($result) > 1)
					$query .= " AND product_id_foreign IN (".implode(",", array_map("intval", $result)).")";
				else
					$query .= " AND product_id_foreign = $result[0]";
				$query .= " AND id_user_foreign = $user_id ORDER BY pa.actif DESC";
				$supplementary_arrays[$i] = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);
			}
			$i++;
		}

		if(sizeof($supplementary_arrays) > 0){
			// Merge all arrays and count values
			$eligible_products = array_count_values(call_user_func_array("array_merge", $supplementary_arrays));
			$product_id = array_keys($eligible_products)[0]; // Product that fits
			return $product_id;
		} else {
			return $result[0];
		}
	} else {
		return null;
	}
}

function postNotification($db, $token, $target, $recipient, $date){
	// To ensure there aren't two notifications about different states of the same target, we deleted every notification regarding the target before inserting the new one.
	$type_token = substr($token, 0, 3);
	$delete_previous_states = $db->query("DELETE FROM team_notifications WHERE notification_token LIKE '%$type_token%' AND notification_target = $target");
	$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_recipient, notification_date, notification_state)
								VALUES('$token', '$target', '$recipient', '$date', '1')");
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

		if($value == -1){
			$query = "UPDATE $table SET $column = NULL";
		} else {
			$query = "UPDATE $table SET $column = '$value'";
		}

		if($table == "tasks"){
			$query .= ", task_last_update = '$now'";
		}

		$query .= " WHERE $primary_key[Column_name] = '$target_id'";

		$update = $db->query($query);
		//echo $query;
	} catch(PDOException $e){
		echo $e->getMessage();
	}
}

function addEntry($db, $table, $column, $value){
	$stmt = $db->prepare("INSERT INTO $table($column) VALUES(?)");
	$stmt->bindParam(1, $value);
	$stmt->execute();
	return $db->lastInsertId();
}
?>
