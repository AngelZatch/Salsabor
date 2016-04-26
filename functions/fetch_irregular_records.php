<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$load = $db->query("SELECT *, IF(date_prolongee IS NOT NULL, date_prolongee,
							IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
							) AS produit_validity FROM passages pg
					JOIN lecteurs_rfid lr ON pg.passage_salle = lr.lecteur_ip
					LEFT JOIN salle s ON lr.lecteur_lieu = s.salle_id
					LEFT JOIN users u ON pg.passage_eleve_id = u.user_id AND pg.passage_eleve_id
					LEFT JOIN produits_adherents pa ON pg.produit_adherent_cible = pa.id_produit_adherent
					LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
					LEFT JOIN cours c ON pg.cours_id = c.cours_id
					WHERE pg.status = 0 OR pg.status = 3
					ORDER BY u.user_nom ASC");

$notifications_settings = $db->query("SELECT * FROM master_settings WHERE user_id = '0'")->fetch(PDO::FETCH_ASSOC);

$recordsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	// Find possible duplicates
	$lower_limit = date("Y-m-d H:i:s", strtotime($details["passage_date"].'-20MINUTES'));
	$upper_limit = date("Y-m-d H:i:s", strtotime($details["passage_date"].'+20MINUTES'));
	$duplicates = $db->query("SELECT passage_id FROM passages
							WHERE passage_eleve = '$details[passage_eleve]'
							AND passage_salle = '$details[passage_salle]'
							AND CASE WHEN cours_id IS NOT NULL
								THEN cours_id = '$details[cours_id]'
							END
							AND passage_date BETWEEN '$lower_limit' AND '$upper_limit'
							AND passage_id != '$details[passage_id]'
							ORDER BY passage_salle DESC")->fetch(PDO::FETCH_COLUMN);

	$r = array();
	$r["duplicates"] = $duplicates;
	$r["id"] = $details["passage_id"];
	$r["card"] = $details["passage_eleve"];
	$r["user_id"] = $details["passage_eleve_id"];
	$r["user"] = $details["user_prenom"]." ".$details["user_nom"];
	$r["photo"] = $details["photo"];
	$r["date"] = $details["passage_date"];
	$r["status"] = $details["status"];
	$r["room"] = $details["salle_name"];
	$r["cours_id"] = $details["cours_id"];
	$r["cours_name"] = $details["cours_intitule"];
	$r["cours_start"] = $details["cours_start"];
	$r["cours_end"] = $details["cours_end"];
	if($details["produit_nom"] != null){
		$r["product_name"] = $details["produit_nom"];
		$r["product_expiration"] = $details["produit_validity"];
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
