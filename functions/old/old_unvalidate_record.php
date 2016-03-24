<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$passage_id = $_POST["passage_id"];
// On retrouve le passage à partir de l'ID
$passage = $db->query("SELECT * FROM passages WHERE passage_id=$passage_id")->fetch(PDO::FETCH_ASSOC);
// On retrouve la participation à partir du passage
$participation = $db->query("SELECT * FROM cours_participants WHERE cours_id_foreign=$passage[cours_id]")->fetch(PDO::FETCH_ASSOC);

$produit = $db->query("SELECT *, produits_adherents.actif AS produitActif
						FROM produits_adherents
						JOIN produits ON id_produit_foreign=produits.produit_id
						WHERE id_produit_adherent=$participation[produit_adherent_id]")->fetch(PDO::FETCH_ASSOC);

$detailCours = $db->query("SELECT * FROM cours JOIN prestations ON cours_type=prestations.prestations_id WHERE cours_id=$passage[cours_id]")->fetch(PDO::FETCH_ASSOC);

$prof = $db->query("SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=$detailCours[prof_principal] AND type_prestation=$detailCours[cours_type]")->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	// Suppression du passage dans la table des participants
	$delete = $db->query("DELETE FROM cours_participants WHERE id=$participation[id]");

	// Réinitilisation de l'enregistrement dans la table passage (indiquera que le passage est de nouveau en attente)
	$update = $db->query("UPDATE passages SET cours_id=NULL, status=0 WHERE passage_id=$passage_id");

	// Rajout du volume horaire dans le forfait
	if($produit["est_illimite"] == 0){
		$restore = $db->prepare("UPDATE produits_adherents SET volume_cours=? WHERE id_produit_adherent=?");
		$remainingHours = $produit["volume_cours"] + $detailCours["cours_unite"];
		$restore->bindParam(1, $remainingHours);
		$restore->bindParam(2, $produit["id_produit_adherent"]);
		$restore->execute();
		if($remainingHours == $produit["volume_horaire"]){
			// Réinitialisation du forfait si nécessaire
			$db->query("UPDATE produits_adherents SET actif=0, date_activation='0000-00-00 00:00:00', date_expiration=NULL WHERE id_produit_adherent=$produit[id_produit_adherent]");
		}
	} else {
		// Réinitialisation du forfait si nécessaire (dans le cas d'un illimité)
		if($db->query("SELECT * FROM cours_participants WHERE produit_adherent_id=$produit[id_produit_adherent]")->rowCount() == 0){
			$db->query("UPDATE produits_adherents SET actif=0, date_activation='0000-00-00 00:00:00', date_expiration=NULL WHERE id_produit_adherent=$produit[id_produit_adherent]");
		}
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
	echo "Passage réinitialisé";
} catch(PDOException $e){
	$db->rollBack();
	var_dump($e->getMessage());
}
?>
