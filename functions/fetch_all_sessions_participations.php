<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$parent_id = $_GET["parent_id"];

$feed = $db->prepare("SELECT s.cours_id, cours_start, COUNT(passage_id) AS crowd FROM cours s
					LEFT JOIN participations pr ON pr.cours_id = s.cours_id
					WHERE cours_parent_id = ?
					GROUP BY s.cours_id");
$feed->bindParam(1, $parent_id, PDO::PARAM_INT);
$feed->execute();

$stats = array();

while($row = $feed->fetch(PDO::FETCH_ASSOC)){
	$date = new DateTime($row["cours_start"]);
	$date = $date->format("Y-m-d");
	$s = array(
		"date" => $date,
		"participations" => $row["crowd"]
	);
	array_push($stats, $s);
}
echo json_encode($stats);

?>
