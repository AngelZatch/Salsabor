<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$session_id = $_GET["session_id"];

$session = $db->query("SELECT cours_salle, cours_start
					FROM cours c
					WHERE cours_id = '$session_id'")->fetch(PDO::FETCH_ASSOC);

$limit_start = date("Y-m-d H:i:s", strtotime($session["cours_start"].'-30MINUTES'));
$limit_end = date("Y-m-d H:i:s", strtotime($session["cours_start"].'+30MINUTES'));

$load = $db->query("SELECT * FROM passages pg
					JOIN lecteurs_rfid lr ON pg.passage_salle = lr.lecteur_ip
					JOIN users u ON pg.passage_eleve = u.user_rfid OR pg.passage_eleve_id = u.user_id
					LEFT JOIN produits_adherents pa ON pg.produit_adherent_cible = pa.id_produit_adherent
					LEFT JOIN produits p ON pa.id_produit_foreign = p.produit_id
					WHERE lecteur_lieu = '$session[cours_salle]' AND cours_id = '$session_id'
					ORDER BY user_nom ASC");

$recordsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$r = array();
	$r["id"] = $details["passage_id"];
	$r["card"] = $details["passage_eleve"];
	$r["user_id"] = $details["passage_eleve_id"];
	$r["user"] = $details["user_prenom"]." ".$details["user_nom"];
	$r["photo"] = $details["photo"];
	$r["date"] = $details["passage_date"];
	$r["status"] = $details["status"];
	if($details["produit_nom"] != null){
		$r["product_name"] = $details["produit_nom"];
	} else {
		$r["product_name"] = "-";
	}
	array_push($recordsList, $r);
}

echo json_encode($recordsList);
?>
