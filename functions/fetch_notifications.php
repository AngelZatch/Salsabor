<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$limit = $_GET["limit"];

if($limit == 0){
	$load = $db->query("SELECT * FROM team_notifications ORDER BY notification_id DESC");
} else {
	$load = $db->query("SELECT * FROM team_notifications ORDER BY notification_id DESC LIMIT $limit");
}

$notificationsList = array();

while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$n = array();
	$n["id"] = $details["notification_id"];
	$n["token"] = $details["notification_token"];
	$n["target"] = $details["notification_target"];
	// Additional details depending of the token
	$n["type"] = substr($n["token"], 0, 3);
	$n["subtype"] = substr($n["token"], 4);
	switch($n["type"]){
		case "PRD": // We have to get the details of the product then
			$sub_query = $db->query("SELECT * FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN users u ON pa.id_user_foreign = u.user_id WHERE id_produit_adherent = '$n[target]'")->fetch(PDO::FETCH_ASSOC);
			$n["product_name"] = $sub_query["produit_nom"];
			$n["product_validity"] = max($sub_query["date_expiration"], $sub_query["date_prolongee"]);
			if(isset($sub_query["date_fin_utilisation"]) && $sub_query["date_fin_utilisation"] != "0000-00-00 00:00:00"){
				$n["product_usage"] = $sub_query["date_fin_utilisation"];
			} else {
				$n["product_usage"] = $n["product_validity"];
			}
			$n["user"] = $sub_query["user_prenom"]." ".$sub_query["user_nom"];
			$n["remaining_hours"] = $sub_query["volume_cours"];
			$n["user_id"] = $sub_query["user_id"];
			$n["photo"] = $sub_query["photo"];
			break;

		case "MAT": // We have to get the details of the maturity
			/*$sub_query = $db->query("SELECT * FROM produits_echeances pe
			JOIN transactions t ON pe.reference_achat = t.id_transaction WHERE produits_echeances_id = '$n[target]'")->fetch(PDO::FETCH_ASSOC);*/
			$sub_query = $db->query("SELECT * FROM produits_echeances pe
									LEFT JOIN transactions t ON pe.reference_achat = t.id_transaction
									LEFT JOIN users u ON t.payeur_transaction = u.user_id
			WHERE produits_echeances_id = '$n[target]'")->fetch(PDO::FETCH_ASSOC);
			$n["payer"] = $sub_query["payeur_echeance"];
			$n["user_id"] = $sub_query["payeur_transaction"];
			$n["maturity_date"] = $sub_query["date_echeance"];
			$n["maturity_value"] = $sub_query["montant"];
			$n["transaction"] = $sub_query["reference_achat"];
			$n["photo"] = $sub_query["photo"];
			break;

		case "TRA":
			break;

		case "MAI": // Here, we only need the user name for the mail address.
			$sub_query = $db->query("SELECT user_prenom, user_nom, photo FROM users u WHERE user_id = '$n[target]'")->fetch(PDO::FETCH_ASSOC);
			$n["user"] = $sub_query["user_prenom"]." ".$sub_query["user_nom"];
			$n["user_id"] = $n["target"];
			$n["photo"] = $sub_query["photo"];
			break;
	}
	$n["date"] = $details["notification_date"];
	$n["status"] = $details["notification_state"];
	array_push($notificationsList, $n);
}
echo json_encode($notificationsList);
?>
