<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["passage_id"];
$passage = $db->query("SELECT passage_date FROM participations WHERE passage_id=$id")->fetch(PDO::FETCH_ASSOC);
/* Pour trouver les cours potentiels pouvant correspondre à ce passage, on cherche tous les cours ayant commencé au plus tôt 60 minutes avant le passage et qui commenceront au plus tard 60 minutes après */
$start = date("Y-m-d H:i:s", strtotime($passage["passage_date"].'-80MINUTES'));
$end = date("Y-m-d H:i:s", strtotime($passage["passage_date"].'+80MINUTES'));
$queryFeed = $db->prepare("SELECT * FROM cours
						JOIN niveau ON cours_niveau=niveau.niveau_id
						JOIN salle ON cours_salle=salle.salle_id
						JOIN users ON prof_principal=users.user_id
						WHERE cours_start>='$start' AND cours_end <='$end'");
$queryFeed->bindValue(1, $id);
$queryFeed->execute();
$cours = array();
while($feed = $queryFeed->fetch(PDO::FETCH_ASSOC)){
	$f = array();
	$f["id"] = $feed["cours_id"];
	$f["nom"] = $feed["cours_intitule"];
	$f["niveau"] = $feed["niveau_name"];
	$f["salle"] = $feed["salle_name"];
	$f["heure"] = date_create($feed["cours_start"])->format("H:i")."-".date_create($feed["cours_end"])->format("H:i");
	$f["prof"] = $feed["user_prenom"]." ".$feed["user_nom"];
	array_push($cours, $f);
}
echo json_encode($cours);
?>
