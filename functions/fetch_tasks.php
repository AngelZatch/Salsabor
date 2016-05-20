<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();

$limit = $_GET["limit"];
$user_id = $_GET["user_id"];

// We dynamically construct the query depending on the flags
$query = "SELECT * FROM tasks t
			LEFT JOIN users u ON t.task_recipient = u.user_id";
if($user_id != 0){
	$query .= " WHERE (task_token LIKE '%USR%' AND task_target = '$user_id')
					OR (task_token LIKE '%PRD%' AND task_target IN (SELECT id_produit_adherent FROM produits_adherents WHERE id_user_foreign = '$user_id'))
					OR (task_token LIKE '%TRA%' AND task_target IN (SELECT id_transaction FROM transactions WHERE payeur_transaction = '$user_id'))
				AND";
} else {
	$query .= " WHERE";
}
$query .= " (task_recipient IS NULL";
if(isset($_SESSION["user_id"])){
	$query .= " OR task_recipient = $_SESSION[user_id]";
}
$query .= ") ORDER BY task_id DESC";
if($limit != 0){
	$query .= " LIMIT $limit";
}
$load = $db->query($query);

$task_list = array();

while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$t = array();
	$t["id"] = $details["task_id"];
	$t["token"] = $details["task_token"];
	$t["target"] = $details["task_target"];
	if($details["task_recipient"] != null){
		$t["recipient"] = $details["user_prenom"]." ".$details["user_nom"];
		$t["recipient_id"] = $details["task_recipient"];
	} else {
		$t["recipient"] = "Affecter un membre";
	}
	// Additional details depending of the token
	$t["type"] = substr($t["token"], 0, 3);
	switch($t["type"]){
		case "USR": // Here, we only need the user name for the mail address.
			$sub_query = $db->query("SELECT CONCAT(user_prenom, ' ', user_nom) AS user, photo FROM users u WHERE user_id = '$t[target]'")->fetch(PDO::FETCH_ASSOC);
			$t["user_id"] = $t["target"];
			$t["link"] = "user/".$t["user_id"];
			break;

		case "PRD":
			$sub_query = $db->query("SELECT user_id, CONCAT(user_prenom, ' ', user_nom) AS user, photo FROM users u WHERE user_id IN (SELECT id_user_foreign FROM produits_adherents WHERE id_produit_adherent ='$t[target]')")->fetch(PDO::FETCH_ASSOC);
			$t["user_id"] = $sub_query["user_id"];
			$t["link"] = "user/".$t["user_id"]."/abonnements";
			break;

		case "TRA":
			$sub_query = $db->query("SELECT user_id, CONCAT(user_prenom, ' ', user_nom) AS user, photo FROM users u WHERE user_id IN (SELECT payeur_transaction FROM transactions WHERE id_transaction = '$t[target]')")->fetch(PDO::FETCH_ASSOC);
			$t["user_id"] = $sub_query["user_id"];
			$t["link"] = "user/".$t["user_id"]."/achats#purchase-".$t["target"];
			break;

		default:
			break;
	}
	$t["user"] = $sub_query["user"];
	$t["photo"] = $sub_query["photo"];
	$t["date"] = $details["task_creation_date"];
	$t["deadline"] = $details["task_deadline"];
	$t["title"] = $details["task_title"];
	if($details["task_description"] != ""){
		$t["description"] = htmlspecialchars_decode($details["task_description"]);
	} else {
		$t["description"] = "Ajouter une description";
	}
	$t["message_count"] = $db->query("SELECT * FROM task_comments WHERE task_id_foreign = '$t[id]'")->rowCount();
	$t["status"] = $details["task_state"];

	// Handling the title's tokens.
	$pattern = "/(![a-z0-9]+!)/i";
	preg_match_all($pattern, $t["title"], $matches, PREG_SET_ORDER);
	foreach($matches as $val){
		switch($val[0]){
			case "!MAIL!":
				$t["title"] = preg_replace("/!MAIL!/", $t["mail"], $t["title"]);
				break;

			case "!USER!":
				$t["title"] = preg_replace("/!USER!/", "<strong>".$t["user"]."</strong>", $t["title"]);
				break;

			case "!PRD!":
				$t["title"] = preg_replace("/!PRD!/", "<strong>".$t["target"]."</strong>", $t["title"]);
				break;

			case "!TRA!":
				$t["title"] = preg_replace("/!TRA!/", "<strong>".$t["target"]."</strong>", $t["title"]);
				break;

			default:
				break;
		}
	}
	array_push($task_list, $t);
}
echo json_encode($task_list);
?>
