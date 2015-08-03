<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$cours = $_POST["cours_id"];

$data = explode(' ', $_POST["adherent"]);
$prenom = $data[0];
$nom = $data[1];

$adherent = $db->query("SELECT * FROM adherents WHERE eleve_nom='$nom' AND eleve_prenom='$prenom'")->fetch(PDO::FETCH_ASSOC);
$produit = $db->query("SELECT * FROM produits_adherents WHERE id_adherent=$adherent[eleve_id] AND actif=1")->fetch(PDO::FETCH_ASSOC);
$detailCours = $db->query("SELECT * FROM cours JOIN prestations ON cours_type=prestations.prestations_id WHERE cours_id=$cours")->fetch(PDO::FETCH_ASSOC);
$prof = $db->query("SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=$detailCours[prof_principal] AND type_prestation=$detailCours[cours_type]")->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	$add = $db->prepare("INSERT INTO cours_participants(cours_id_foreign, eleve_id_foreign, produit_adherent_id)
												VALUES(:cours, :eleve, :produit)");
	$add->bindParam(':cours', $cours);
	$add->bindParam(':eleve', $adherent["eleve_id"]);
	$add->bindParam(':produit', $forfait["id_transaction"]);
	$add->execute();
	
	if(isset($produit["id_transcation"])){
		// Déduction du volume horaire dans le forfait
		$substract = $db->prepare("UPDATE produits_adherents SET volume_cours=? WHERE id_transaction=?");
		$remainingHours = $produit["volume_cours"] - $detailCours["cours_unite"];
		$substract->bindParam(1, $remainingHours);
		$substract->bindParam(2, $produit["id_transaction"]);
		$substract->execute();
	}
	
	// Mise à jour de la rémunération du professeur
	if($prof["ratio_multiplicatif"] == "personne"){
		$prix = $detailCours["cours_prix"] + $prof["tarif_prestation"];
		$add = $db->prepare("UPDATE cours SET cours_prix=? WHERE cours_id=?");
		$add->bindParam(1, $prix);
		$add->bindParam(2, $cours);
		$add->execute();
	}
	
	$db->commit();
	echo $adherent["eleve_id"];
} catch (PDOExecption $e) {
	$db->rollBack();
	var_dump($e->getMessage());
}
?>