<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

try{
	$db->beginTransaction();
	$new = $db->prepare('INSERT INTO adherents(eleve_prenom, eleve_nom, numero_rfid, date_naissance, date_inscription, rue, code_postal, ville, mail, telephone)
	VALUES(:prenom, :nom, :rfid, :date_naissance, :date_inscription, :rue, :code_postal, :ville, :mail, :telephone)');
	$new->bindParam(':prenom', $_POST['identite_prenom']);
	$new->bindParam(':nom', $_POST['identite_nom']);
	$new->bindParam(':rfid', $_POST["rfid"]);
	$new->bindParam(':date_naissance', $_POST['date_naissance']);
	$new->bindParam(':date_inscription', date_create('now')->format('Y-m-d'));
	$new->bindParam(':rue', $_POST['rue']);
	$new->bindParam(':code_postal', $_POST['code_postal']);
	$new->bindParam(':ville', $_POST['ville']);
	$new->bindParam(':mail', $_POST['mail']);
	$new->bindParam(':telephone', $_POST['telephone']);
	$new->execute();
	if(isset($_POST["rfid"])){
		$delete = $db->prepare("DELETE FROM passages WHERE passage_eleve=? AND status=1");
		$delete->bindParam(1, $_POST["rfid"]);
		$delete->execute();
	}
	$db->commit();
	echo "Inscription réalisée.";
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>