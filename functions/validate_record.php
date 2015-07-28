<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$cours = $_POST["cours_id"];
$eleve = $_POST["eleve_id"];
$rfid = $_POST["rfid"];
$produit = $db->query("SELECT id_transaction FROM produits_adherents WHERE id_adherent=$eleve")->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	// Enregistrement du passage dans la table des participants
	$new = $db->prepare('INSERT INTO cours_participants(cours_id_foreign, eleve_id_foreign, produit_adherent_id)
	VALUES(:cours, :eleve, :produit)');
	$new->bindParam(':cours', $cours);
	$new->bindParam(':eleve', $eleve);
	$new->bindParam(':produit', $produit["id_transaction"]);
	$new->execute();
	
	// Validation de l'enregistrement dans la table passage (indiquera que le passage a déjà été traité)
	$update = $db->prepare("UPDATE passages SET status=2 WHERE passage_eleve=?");
	$update->bindParam(1, $rfid);
	$update->execute();
	
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>