<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();

$limit = $_GET["limit"];
$user_id = $_GET["user_id"];
$attached_id = $_GET["attached_id"];
$filter = $_GET["filter"];

// We dynamically construct the query depending on the flags
$query = "SELECT *, CONCAT (u.user_prenom, ' ', u.user_nom) AS recipient, CONCAT (u2.user_prenom, ' ', u2.user_nom) AS creator FROM tasks t
			LEFT JOIN users u ON t.task_recipient = u.user_id
			LEFT JOIN users u2 ON t.task_creator = u2.user_id
			LEFT JOIN assoc_task_tags at ON t.task_id = at.task_id_foreign";
if($user_id != 0 || $attached_id != 0 || $filter != ""){
	$query .= " WHERE";
}
if($user_id != 0){
	$query .= " (task_token LIKE '%USR%' AND task_target = '$user_id')
					OR (task_token LIKE '%PRD%' AND task_target IN (SELECT id_produit_adherent FROM produits_adherents WHERE id_user_foreign = '$user_id'))
					OR (task_token LIKE '%TRA%' AND task_target IN (SELECT id_transaction FROM transactions WHERE payeur_transaction = '$user_id'))";
}
//$query .= " (task_recipient IS NULL OR task_recipient = 0 OR";
if($user_id != 0 && $attached_id != 0){
	$query .= " AND";
}
if($attached_id != 0){
	$query .= " (task_recipient = $attached_id OR tag_id_foreign IN (SELECT tag_id_foreign FROM assoc_user_tags WHERE user_id_foreign = $attached_id))";
}
if($attached_id != 0 && $filter != ""){
	$query .= " AND";
}
if($filter == "pending"){
	$query .= " task_state = 0";
} else if($filter == "done"){
	$query .= " task_state = 1";
}
$query .= " ORDER BY task_id DESC";
if($limit != 0){
	$query .= " LIMIT $limit";
}
/*echo $query;*/
$load = $db->query($query);
$task_list = array();

while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$t = array();
	$t["id"] = $details["task_id"];
	$t["token"] = $details["task_token"];
	$t["target"] = $details["task_target"];
	if($details["task_recipient"] != null && $details["task_recipient"] != 0){
		$t["recipient"] = $details["recipient"];
		$t["recipient_id"] = $details["task_recipient"];
	} else {
		$t["recipient"] = "";
	}
	if($details["task_creator"] == null){
		$t["creator"] = "Système";
	} else {
		$t["creator"] = $details["creator"];
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

	// Tags
	$labels = $db->query("SELECT * FROM assoc_task_tags ur
						JOIN tags_user tu ON ur.tag_id_foreign = tu.rank_id
						WHERE task_id_foreign = '$t[id]'");
	$t["labels"] = array();
	while($label = $labels->fetch(PDO::FETCH_ASSOC)){
		$l = array();
		$l["entry_id"] = $label["entry_id"];
		$l["tag_color"] = $label["tag_color"];
		$l["rank_name"] = $label["rank_name"];
		array_push($t["labels"], $l);
	}

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
