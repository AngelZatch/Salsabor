<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$cours = $_POST["cours_id"];
$eleve = $_POST["eleve_id"];
$rfid = $_POST["rfid"];
$produit = $db->query("SELECT id_transaction FROM produits_adherents WHERE id_adherent=$eleve")->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	// Suppression du passage dans la table des participants
	$new = $db->prepare('DELETE FROM cours_participants WHERE cours_id_foreign=:cours AND eleve_id_foreign=:eleve AND produit_adherent_id=:produit');
	$new->bindParam(':cours', $cours);
	$new->bindParam(':eleve', $eleve);
	$new->bindParam(':produit', $produit["id_transaction"]);
	$new->execute();
	
	// Réinitilisation de l'enregistrement dans la table passage (indiquera que le passage est de nouveau en attente)
	$update = $db->prepare("UPDATE passages SET cours_id=NULL, status=0 WHERE passage_eleve=?");
	$update->bindParam(1, $rfid);
	$update->execute();
	
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>