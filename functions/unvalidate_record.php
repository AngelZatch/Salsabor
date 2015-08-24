<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$cours = $_POST["cours_id"];
$eleve = $_POST["eleve_id"];
$passage = $_POST["passage_id"];
$rfid = $_POST["rfid"];
$produit = $db->query("SELECT *, produits_adherents.actif AS produitActif FROM produits_adherents JOIN produits ON id_produit=produits.produit_id WHERE id_adherent=$eleve")->fetch(PDO::FETCH_ASSOC);
$detailCours = $db->query("SELECT * FROM cours JOIN prestations ON cours_type=prestations.prestations_id WHERE cours_id=$cours")->fetch(PDO::FETCH_ASSOC);
$prof = $db->query("SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=$detailCours[prof_principal] AND type_prestation=$detailCours[cours_type]")->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	// Suppression du passage dans la table des participants
	$delete = $db->prepare('DELETE FROM cours_participants WHERE cours_id_foreign=:cours AND eleve_id_foreign=:eleve AND produit_adherent_id=:produit');
	$delete->bindParam(':cours', $cours);
	$delete->bindParam(':eleve', $eleve);
	$delete->bindParam(':produit', $produit["id_transaction"]);
	$delete->execute();
	
	// Réinitilisation de l'enregistrement dans la table passage (indiquera que le passage est de nouveau en attente)
	$update = $db->prepare("UPDATE passages SET cours_id=NULL, status=0 WHERE passage_id=?");
	$update->bindParam(1, $passage);
	$update->execute();
	
	// Rajout du volume horaire dans le forfait
	if(!strstr($produit["produit_nom"], "Illimité")){
		$restore = $db->prepare("UPDATE produits_adherents SET volume_cours=? WHERE id_transaction=?");
		$remainingHours = $produit["volume_cours"] + $detailCours["cours_unite"];
		$restore->bindParam(1, $remainingHours);
		$restore->bindParam(2, $produit["id_transaction"]);
		$restore->execute();
	}
	
	// Mise à jour de la rémunération du professeur
	if($prof["ratio_multiplicatif"] == "personne"){
		$prix = $detailCours["cours_prix"] - $prof["tarif_prestation"];
		$add = $db->prepare("UPDATE cours SET cours_prix=? WHERE cours_id=?");
		$add->bindParam(1, $prix);
		$add->bindParam(2, $cours);
		$add->execute();
	}
	
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>