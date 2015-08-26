<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$cours = $_POST["cours_id"];
$eleve = $_POST["delete_id"];

$produit = $db->query("SELECT * FROM produits_adherents WHERE id_user_foreign=$eleve")->fetch(PDO::FETCH_ASSOC);
$detailCours = $db->query("SELECT * FROM cours JOIN prestations ON cours_type=prestations.prestations_id WHERE cours_id=$cours")->fetch(PDO::FETCH_ASSOC);
$prof = $db->query("SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=$detailCours[prof_principal] AND type_prestation=$detailCours[cours_type]")->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	$delete = $db->prepare('DELETE FROM cours_participants WHERE cours_id_foreign=:cours AND eleve_id_foreign=:eleve');
	$delete->bindParam(':cours', $cours);
	$delete->bindParam(':eleve', $eleve);
	$delete->execute();
	
	if(isset($produit["id_produit_adherent"])){
		// Rajout du volume horaire dans le forfait
		if(!strstr($produit["produit_nom"], "Illimité")){
			$restore = $db->prepare("UPDATE produits_adherents SET volume_cours=? WHERE id_produit_adherent=?");
			$remainingHours = $produit["volume_cours"] + $detailCours["cours_unite"];
			$restore->bindParam(1, $remainingHours);
			$restore->bindParam(2, $produit["id_produit_adherent"]);
			$restore->execute();
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
} catch (PDOExecption $e) {
	$db->rollBack();
	$message = var_dump($e->getMessage());
}
?>