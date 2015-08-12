<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$update_id = $_POST["update_id"];

try{
	$db->beginTransaction();
	// Mise à jour du tarif professeur
	$update = $db->prepare('UPDATE tarifs_professeurs SET tarif_prestation=:tarif WHERE tarif_professeur_id=:update_id');
	$update->bindParam(':tarif', $_POST["tarif"]);
	$update->bindParam(':update_id', $update_id);
	$update->execute();
	
	// Mise à jour de tous les prix de tous les cours non payés affectés par le changement
	// Selection de tous les cours affectés par le changement du tarif
	$queryTarif = $db->query("SELECT * FROM tarifs_professeurs WHERE tarif_professeur_id='$update_id'")->fetch(PDO::FETCH_ASSOC);
	$queryCours = $db->prepare("SELECT * FROM cours WHERE prof_principal=? AND cours_type=? AND paiement_effectue=0");
	$queryCours->bindParam(1, $queryTarif["prof_id_foreign"]);
	$queryCours->bindParam(2, $queryTarif["type_prestation"]);
	$queryCours->execute();
	
	$test = 0; 
	while($cours = $queryCours->fetch(PDO::FETCH_ASSOC)){
		/* CAS 1 : tarif par personne */
		if($queryTarif["ratio_multiplicatif"] == 'personne'){
			// Compte de tous les participants n'ayant pas utilisé d'invitation
			$queryParticipants = $db->query("SELECT * FROM cours_participants JOIN produits_adherents ON produit_adherent_id=produits_adherents.id_transaction JOIN produits ON id_produit=produits.produit_id WHERE cours_id_foreign='$cours[cours_id]' AND produit_nom != 'Invitation'")->rowCount();
			$value = $queryParticipants * $_POST["tarif"];
		} else if($queryTarif["ratio_multiplicatif"] == "heure"){
			// Calcul basé sur le nombre d'heures
			$value = $queryCours["cours_unite"] * $_POST["tarif"];
		} else {
			// Tarif "prestation" ; remplacement par le nouveau tarif
			$value = $_POST["tarif"];
		}
		// Application du nouveau prix
		$db->query("UPDATE cours SET cours_prix='$value' WHERE cours_id='$cours[cours_id]'");
		$test++;
	}
	
	$db->commit();
	echo "Tarif mis à jour. ".$test." cours affectés par la modification";
} catch (PDOExecption $e) {
	$db->rollBack();
	$message = var_dump($e->getMessage());
	$data = array('type' => 'error', 'message' => ' '.$message);
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
}
?>