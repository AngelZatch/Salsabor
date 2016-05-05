<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$load = $db->query("SELECT u.user_id, user_prenom, user_nom, COUNT(u.user_id) AS count
					FROM participations pr
					LEFT JOIN users u ON pr.user_id = u.user_id
					WHERE (pr.status = 0 OR pr.status = 3 OR (pr.status = 2 AND (produit_adherent_id IS NULL OR produit_adherent_id = '' OR produit_adherent_id = 0)))
					GROUP BY user_id
					ORDER BY user_nom ASC");

$user_list = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){

	$u = array();
	$u["user_id"] = $details["user_id"];
	$u["user"] = $details["user_prenom"]." ".$details["user_nom"];
	$u["count"] = $details["count"];
	array_push($user_list, $u);
}

echo json_encode($user_list);
?>
