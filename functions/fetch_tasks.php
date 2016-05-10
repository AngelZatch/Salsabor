<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$limit = $_GET["limit"];
$user_id = $_GET["user_id"];

// We dynamically construct the query depending on the flags
$query = "SELECT * FROM tasks ";
if($user_id != 0){
	$query .= "WHERE task_token LIKE '%USR%' AND task_target = '$user_id' ";
}
$query .= "ORDER BY task_id DESC";
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
	// Additional details depending of the token
	$t["type"] = substr($t["token"], 0, 3);
	$t["subtype"] = substr($t["token"], 4);
	switch($t["type"]){
		case "USR": // Here, we only need the user name for the mail address.
			$sub_query = $db->query("SELECT CONCAT(user_prenom, ' ', user_nom) AS user, photo FROM users u WHERE user_id = '$t[target]'")->fetch(PDO::FETCH_ASSOC);
			$t["user"] = $sub_query["user"];
			$t["user_id"] = $t["target"];
			$t["photo"] = $sub_query["photo"];
			break;
	}
	$t["date"] = $details["task_creation_date"];
	$t["deadline"] = $details["task_deadline"];
	$t["title"] = $details["task_title"];
	$t["description"] = $details["task_description"];
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
				$user = "Andréas Pinbouen";
				$t["title"] = preg_replace("/!USER!/", "<strong>".$t["user"]."</strong>", $t["title"]);
				break;
		}
	}
	array_push($task_list, $t);
}
echo json_encode($task_list);
?>
