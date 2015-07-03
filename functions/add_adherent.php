<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

try{
	$db->beginTransaction();
	$new = $db->prepare('INSERT INTO adherents(eleve_prenom, eleve_nom, date_naissance, date_inscription, rue, code_postal, ville, mail, telephone)
	VALUES(:prenom, :nom, :date_naissance, :date_inscription, :rue, :code_postal, :ville, :mail, :telephone)');
	$new->bindParam(':prenom', $_POST['identite_prenom']);
	$new->bindParam(':nom', $_POST['identite_nom']);
	$new->bindParam(':date_naissance', $_POST['date_naissance']);
	$new->bindParam(':date_inscription', date_create('now')->format('Y-m-d'));
	$new->bindParam(':rue', $_POST['rue']);
	$new->bindParam(':code_postal', $_POST['code_postal']);
	$new->bindParam(':ville', $_POST['ville']);
	$new->bindParam(':mail', $_POST['mail']);
	$new->bindParam(':telephone', $_POST['telephone']);
	$new->execute();
	$db->commit();
	echo "Succès lors de l'ajout";
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>