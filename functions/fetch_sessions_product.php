<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$product_id = $_POST["product_id"];

$load = $db->query("SELECT * FROM cours_participants cp
					JOIN cours c ON cp.cours_id_foreign = c.cours_id
					WHERE produit_adherent_id='$product_id'");

$sessionsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$s = array();
	$s["title"] = $details["cours_intitule"];
	$s["start"] = $details["cours_start"];
	$s["end"] = $details["cours_end"];
	array_push($sessionsList, $s);
}

echo json_encode($sessionsList);
?>
