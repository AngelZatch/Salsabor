<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$filter = $_GET["filter"];

if($filter == "active"){
	$queryList = $db->query("SELECT * FROM users u
						WHERE user_id NOT IN (SELECT user_id_foreign FROM assoc_user_tags ur JOIN tags_user tu ON ur.tag_id_foreign = tu.rank_id WHERE rank_name = 'Professeur')
						AND user_id NOT IN (SELECT user_id_foreign FROM assoc_user_tags ur JOIN tags_user tu ON ur.tag_id_foreign = tu.rank_id WHERE rank_name = 'Staff')
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
						WHERE user_id IN (SELECT user_id_foreign FROM assoc_user_tags ur JOIN tags_user tu ON ur.tag_id_foreign = tu.rank_id WHERE rank_name = 'Staff')
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
