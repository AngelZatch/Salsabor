<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];

$load = $db->query("SELECT id_produit_adherent, pa.actif AS produit_adherent_actif, produit_nom FROM produits_adherents pa
					JOIN produits p
						ON pa.id_produit_foreign = p.produit_id
					WHERE id_user_foreign =
						(SELECT eleve_id_foreign
						FROM cours_participants
						WHERE id='$record_id')
						AND pa.actif != '2'
						AND est_abonnement != '1'");

$productList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$p = array();
	$p["id"] = $details["id_produit_adherent"];
	$p["status"] = $details["produit_adherent_actif"];
	$p["title"] = $details["produit_nom"];
	array_push($productList, $p);
}
echo json_encode($productList);
?>
