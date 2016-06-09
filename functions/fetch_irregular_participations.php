<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$participation_id = $_GET["participation_id"];
if($participation_id == 0){
	$participation_id = PHP_INT_MAX;
}

$load = $db->query("SELECT *, pr.user_rfid AS pr_rfid FROM participations pr
					LEFT JOIN readers re ON pr.room_token = re.reader_token
					LEFT JOIN rooms r ON re.reader_id = r.room_reader
					LEFT JOIN users u ON pr.user_id = u.user_id
					LEFT JOIN produits_adherents pa ON pr.produit_adherent_id = pa.id_produit_adherent
					LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
					LEFT JOIN cours c ON pr.cours_id = c.cours_id
					WHERE (pr.status = 0 OR pr.status = 3 OR (pr.status = 2 AND (produit_adherent_id IS NULL OR produit_adherent_id = '' OR produit_adherent_id = 0)))
					AND passage_id < '$participation_id'
					ORDER BY pr.passage_id DESC
					LIMIT 30");

$notifications_settings = $db->query("SELECT * FROM master_settings WHERE user_id = '0'")->fetch(PDO::FETCH_ASSOC);

$recordsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	// Find possible duplicates
	$lower_limit = date("Y-m-d H:i:s", strtotime($details["passage_date"].'-20MINUTES'));
	$upper_limit = date("Y-m-d H:i:s", strtotime($details["passage_date"].'+20MINUTES'));
	$duplicates = $db->query("SELECT passage_id FROM participations
							WHERE user_rfid = '$details[user_rfid]'
							AND room_token = '$details[room_token]'
							AND CASE WHEN cours_id IS NOT NULL
								THEN cours_id = '$details[cours_id]'
							END
							AND passage_date BETWEEN '$lower_limit' AND '$upper_limit'
							AND passage_id != '$details[passage_id]'
							ORDER BY room_token DESC")->fetch(PDO::FETCH_COLUMN);

	$r = array();
	$r["duplicates"] = $duplicates;
	$r["id"] = $details["passage_id"];
	$r["card"] = $details["pr_rfid"];
	$r["user_id"] = $details["user_id"];
	$r["user"] = $details["user_prenom"]." ".$details["user_nom"];
	$r["photo"] = $details["photo"];
	$r["date"] = $details["passage_date"];
	$r["status"] = $details["status"];
	$r["room"] = $details["room_name"];
	$r["cours_id"] = $details["cours_id"];
	$r["cours_name"] = $details["cours_intitule"];
	$r["cours_start"] = $details["cours_start"];
	$r["cours_end"] = $details["cours_end"];
	if($details["produit_nom"] != null){
		$r["product_name"] = $details["produit_nom"];
		$r["product_expiration"] = max($details["date_expiration"], $details["date_fin_utilisation"], $details["date_prolongee"]);
		if($details["est_illimite"] == "1"){
			$r["product_hours"] = 9999;
		} else {
			$r["product_hours"] = $details["volume_cours"];
		}
	} else {
		$r["product_name"] = "-";
	}
	$r["days_before_exp"] = $notifications_settings["days_before_exp"];
	$r["hours_before_exp"] = $notifications_settings["hours_before_exp"];
	array_push($recordsList, $r);
}

echo json_encode($recordsList);
?>
