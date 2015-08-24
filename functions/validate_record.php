<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$cours = $_POST["cours_id"];
$eleve = $_POST["eleve_id"];
$passage = $_POST["passage_id"];
$rfid = $_POST["rfid"];
$detailCours = $db->query("SELECT * FROM cours JOIN prestations ON cours_type=prestations.prestations_id WHERE cours_id=$cours")->fetch(PDO::FETCH_ASSOC);
$prof = $db->query("SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=$detailCours[prof_principal] AND type_prestation=$detailCours[cours_type]")->fetch(PDO::FETCH_ASSOC);

$date_now = date_create("now")->format("Y-m-d 00:00:00");

try{
	$db->beginTransaction();
	
	// Vérification de la présence d'une invitation
	$queryInvitation = $db->query("SELECT *, produits_adherents.actif AS produitActif FROM produits_adherents JOIN produits ON id_produit=produits.produit_id WHERE id_adherent=$eleve AND produit_nom='Invitation' AND produits_adherents.actif='1'");
	if($queryInvitation->rowCount() == '1'){
		$produit = $queryInvitation->fetch(PDO::FETCH_ASSOC);
		$actif = 0;
		
		// Désactivation de l'invitation
		$deactivate = $db->prepare("UPDATE produits_adherents SET date_fin_utilisation=?, actif=? WHERE id_transaction=?");
		$deactivate->bindParam(1, $date_now);
		$deactivate->bindParam(2, $actif);
		$deactivate->bindParam(3, $produit["id_transaction"]);
		$deactivate->execute();
	} else {
		$produit = $db->query("SELECT *, produits_adherents.actif AS produitActif FROM produits_adherents JOIN produits ON id_produit=produits.produit_id WHERE id_adherent=$eleve AND produit_nom!='Invitation'")->fetch(PDO::FETCH_ASSOC);
		// Vérification de la validité du forfait et activation si nécessaire
		if($produit["produitActif"] == '0'){
			$actif = 1;
			$date_activation = $date_now;
			$date_expiration = date("Y-m-d 00:00:00", strtotime($date_activation.'+'.$produit["validite_initiale"].'DAYS'));
			$queryHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee >= ? AND date_chomee <= ?");
			$queryHoliday->bindParam(1, $date_activation);
			$queryHoliday->bindParam(2, $date_expiration);
			$queryHoliday->execute();

			$j = 0;

			for($i = 1; $i <= $queryHoliday->rowCount(); $i++){
				$exp_date = date("Y-m-d 00:00:00",strtotime($date_expiration.'+'.$i.'DAYS'));
				$checkHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee=?");
				$checkHoliday->bindParam(1, $exp_date);
				$checkHoliday->execute();
				if($checkHoliday->rowCount() != 0){
					$j++;
				}
				$totalOffset = $i + $j;
				$new_exp_date = date("Y-m-d 00:00:00",strtotime($date_expiration.'+'.$totalOffset.'DAYS'));
			}

			$activate = $db->prepare("UPDATE produits_adherents SET date_activation=?, date_expiration=?, actif=? WHERE id_transaction=?");
			$activate->bindParam(1, $date_activation);
			$activate->bindParam(2, $new_exp_date);
			$activate->bindParam(3, $actif);
			$activate->bindParam(4, $produit["id_transaction"]);
			$activate->execute();
		}
		
		// Déduction du volume horaire dans le forfait
		if(isset($produit["id_transaction"])){
			if(!strstr($produit["produit_nom"], "Illimité")){
				$substract = $db->prepare("UPDATE produits_adherents SET volume_cours=? WHERE id_transaction=?");
				$remainingHours = $produit["volume_cours"] - $detailCours["cours_unite"];
				$substract->bindParam(1, $remainingHours);
				$substract->bindParam(2, $produit["id_transaction"]);
				$substract->execute();
			}
		}
		
		// Mise à jour de la rémunération du professeur
		if($prof["ratio_multiplicatif"] == "personne"){
			$prix = $detailCours["cours_prix"] + $prof["tarif_prestation"];
			$add = $db->prepare("UPDATE cours SET cours_prix=? WHERE cours_id=?");
			$add->bindParam(1, $prix);
			$add->bindParam(2, $cours);
			$add->execute();
		}
	}
	
	// Enregistrement du passage dans la table des participants
	$new = $db->prepare('INSERT INTO cours_participants(cours_id_foreign, eleve_id_foreign, produit_adherent_id)
	VALUES(:cours, :eleve, :produit)');
	$new->bindParam(':cours', $cours);
	$new->bindParam(':eleve', $eleve);
	$new->bindParam(':produit', $produit["id_transaction"]);
	$new->execute();
	
	// Validation de l'enregistrement dans la table passage (indiquera que le passage a déjà été traité)
	$update = $db->prepare("UPDATE passages SET cours_id=?, status=2 WHERE passage_id=?");
	$update->bindParam(1, $cours);
	$update->bindParam(2, $passage);
	$update->execute();
	
	$db->commit();
	echo "Passage enregistré.";
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>