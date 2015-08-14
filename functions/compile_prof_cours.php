<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$prof_id = $_POST["prof_id"];

// Liste des cours
$queryCoursProf = $db->query("SELECT * FROM cours WHERE prof_principal=$prof_id");
$result = array();
while($coursProf = $queryCoursProf->fetch(PDO::FETCH_ASSOC)){
	$h = array();
	$h["day"] = $coursProf["cours_start"];
	$h["cours_nom"] = $coursProf["cours_intitule"];
	array_push($result, $h);
}
echo json_encode($result);
?>