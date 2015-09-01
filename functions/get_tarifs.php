<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();
// Tarifs
$queryTarifs = $db->prepare('SELECT * FROM tarifs_professeurs JOIN prestations ON type_prestation=prestations.prestations_id WHERE prof_id_foreign=?');
$queryTarifs->bindValue(1, $_POST["id"]);
$queryTarifs->execute();
$result = array();
while($tarifs = $queryTarifs->fetch(PDO::FETCH_ASSOC)){
	$t = array();
	$t["id"] = $tarifs["tarif_professeur_id"];
	$t["prestation_id"] = $tarifs["type_prestation"];
	$t["prestation"] = $tarifs["prestations_name"];
	$t["tarif"] = $tarifs["tarif_prestation"];
	$t["ratio"] = $tarifs["ratio_multiplicatif"];
	array_push($result, $t);
}
echo json_encode($result);
?>
