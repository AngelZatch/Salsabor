<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$filter = $_GET["filter"];

if($filter == "active"){
	$queryList = $db->query("SELECT * FROM users u
						WHERE est_professeur != 1
						AND est_staff != 1
						AND actif = 1
						ORDER BY user_nom DESC");
	$userList = array();
	while($user = $queryList->fetch(PDO::FETCH_ASSOC)){
		$u = array();
		$u["id"] = $user["user_id"];
		$u["user"] = $user["user_prenom"]." ".$user["user_nom"];
		array_push($userList, $u);
	}
} else if($filter == "staff"){
	$queryList = $db->query("SELECT * FROM users u
						WHERE est_staff = 1
						ORDER BY user_nom DESC");
	$userList = array();
	while($user = $queryList->fetch(PDO::FETCH_ASSOC)){
		$u = array();
		$u["id"] = $user["user_id"];
		$u["user"] = $user["user_prenom"]." ".$user["user_nom"];
		array_push($userList, $u);
	}
}
echo json_encode($userList);
?>
