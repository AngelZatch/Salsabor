<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$eleve = $_POST["eleve_id"];
$cours = $_POST["cours_id"];
$forfait = $_POST["produit_id"];
$produit = $db->query("SELECT id_transaction, volume_cours FROM produits_adherents WHERE id_adherent=$eleve")->fetch(PDO::FETCH_ASSOC);
$detailCours = $db->query("SELECT * FROM cours JOIN prestations ON cours_type=prestations.prestations_id WHERE cours_id=$cours")->fetch(PDO::FETCH_ASSOC);
$prof = $db->query("SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=$detailCours[prof_principal] AND type_prestation=$detailCours[cours_type]")->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	$update = $db->prepare('UPDATE cours_participants SET produit_adherent_id=? WHERE cours_id_foreign=? AND eleve_id_foreign=?');
	$update->bindParam(1, $forfait);
	$update->bindParam(2, $cours);
	$update->bindParam(3, $eleve);
	$update->execute();
	
	// Déduction du volume horaire dans le forfait
	$substract = $db->prepare("UPDATE produits_adherents SET volume_cours=? WHERE id_transaction=?");
	$remainingHours = $produit["volume_cours"] - $detailCours["cours_unite"];
	$substract->bindParam(1, $remainingHours);
	$substract->bindParam(2, $produit["id_transaction"]);
	$substract->execute();
	
	// Mise à jour de la rémunération du professeur
	if($prof["ratio_multiplicatif"] == "personne"){
		$prix = $detailCours["cours_prix"] + $prof["tarif_prestation"];
		$add = $db->prepare("UPDATE cours SET cours_prix=? WHERE cours_id=?");
		$add->bindParam(1, $prix);
		$add->bindParam(2, $cours);
		$add->execute();
	}
	
	$db->commit();
	echo "Forfait lié.";
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>