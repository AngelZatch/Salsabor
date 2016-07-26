<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["eleve_id"];
$queryFeed = $db->prepare('SELECT *, pa.date_activation AS dateActivation, pa.actif AS produitActif
								FROM produits_adherents pa
								JOIN users u ON id_user_foreign=u.user_id
								JOIN produits p ON id_produit_foreign=p.product_id
								LEFT OUTER JOIN transactions t
									ON id_transaction_foreign=t.id_transaction
									AND t.id_transaction IS NOT NULL
								WHERE id_user_foreign=? AND est_abonnement=0
								ORDER BY produitActif DESC');
$queryFeed->bindValue(1, $id);
$queryFeed->execute();
$cours = array();
while($feed = $queryFeed->fetch(PDO::FETCH_ASSOC)){
	$f = array();
	$f["id"] = $feed["id_produit_adherent"];
	$f["nom"] = $feed["product_name"];
	$f["actif"] = $feed["produitActif"];
	if($f["actif"] == 1){
		$f["validite"] = date_create($feed["dateActivation"])->format('d/m/Y')." - ".date_create($feed["date_expiration"])->format('d/m/Y');
	} else {
		$f["validite"] = "Premier passage";
	}
	if($feed["est_illimite"] == 1){
		$f["solde"] = "Illimité";
	} else {
		$f["solde"] = $feed["volume_cours"]." h";
	}
	array_push($cours, $f);
}
echo json_encode($cours);
?>
