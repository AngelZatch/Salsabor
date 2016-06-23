<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$prof_id = $_POST["prof_id"];

// Liste des cours
$stmt = $db->prepare("SELECT * FROM cours WHERE prof_principal=?");
$stmt->bindParam(1, $prof_id, PDO::PARAM_INT);
$stmt->execute();
$result = array();
while($coursProf = $stmt->fetch(PDO::FETCH_ASSOC)){
	$h = array();
	$h["day"] = $coursProf["cours_start"];
	$h["cours_nom"] = $coursProf["cours_intitule"];
	array_push($result, $h);
}
echo json_encode($result);
?>
