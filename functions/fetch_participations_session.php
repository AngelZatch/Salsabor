<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$session_id = $_GET["session_id"];

$session = $db->query("SELECT cours_salle, cours_start
					FROM cours c
					WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_ASSOC);

$limit_start = date("Y-m-d H:i:s", strtotime($session["cours_start"].'-30MINUTES'));
$limit_end = date("Y-m-d H:i:s", strtotime($session["cours_start"].'+30MINUTES'));

$load = $db->query("SELECT *, IF(date_prolongee IS NOT NULL, date_prolongee,
							IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
							) AS produit_validity FROM participations pr
					LEFT JOIN lecteurs_rfid lr ON pr.room_token = lr.lecteur_ip
					LEFT JOIN salle s ON lr.lecteur_lieu = s.salle_id
					LEFT JOIN users u ON pr.user_id = u.user_id
					LEFT JOIN produits_adherents pa ON pr.produit_adherent_id = pa.id_produit_adherent
					LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
					LEFT JOIN cours c ON pr.cours_id = c.cours_id
					WHERE lr.lecteur_lieu = '$session[cours_salle]' AND pr.cours_id = '$session_id'
					ORDER BY u.user_nom ASC");

$notifications_settings = $db->query("SELECT * FROM master_settings WHERE user_id = '0'")->fetch(PDO::FETCH_ASSOC);

$recordsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$r = array();
	$r["id"] = $details["passage_id"];
	$r["card"] = $details["user_rfid"];
	$r["user_id"] = $details["user_id"];
	$r["user"] = $details["user_prenom"]." ".$details["user_nom"];
	$r["photo"] = $details["photo"];
	$r["date"] = $details["passage_date"];
	$r["status"] = $details["status"];
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
	$r["count"] = $db->query("SELECT * FROM tasks
					WHERE ((task_token LIKE '%USR%' AND task_target = '$r[user_id]')
					OR (task_token LIKE '%PRD%' AND task_target IN (SELECT id_produit_adherent FROM produits_adherents WHERE id_user_foreign = '$r[user_id]'))
					OR (task_token LIKE '%TRA%' AND task_target IN (SELECT id_transaction FROM transactions WHERE payeur_transaction = '$r[user_id]')))
						AND task_state = 0")->rowCount();
	array_push($recordsList, $r);
}

echo json_encode($recordsList);
?>
