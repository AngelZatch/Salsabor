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
			switch($t["subtype"]){
				case "MAI":
					$sub_query = $db->query("SELECT user_prenom, user_nom, photo FROM users u WHERE user_id = '$t[target]'")->fetch(PDO::FETCH_ASSOC);
					$t["user"] = $sub_query["user_prenom"]." ".$sub_query["user_nom"];
					$t["user_id"] = $t["target"];
					$t["photo"] = $sub_query["photo"];
					break;
			}
			break;
	}
	$t["date"] = $details["task_creation_date"];
	$t["deadline"] = $details["task_deadline"];
	$t["title"] = $details["task_title"];
	$t["description"] = $details["task_description"];
	$t["message_count"] = $db->query("SELECT * FROM task_comments WHERE task_id_foreign = '$t[id]'")->rowCount();
	$t["status"] = $details["task_state"];
	array_push($task_list, $t);
}
echo json_encode($task_list);
?>
