<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$accesWeb = 1;
$estEleve = 1;

try{
	$db->beginTransaction();
	$new = $db->prepare('INSERT INTO users(user_prenom, user_nom, user_rfid, date_naissance, date_inscription, rue, code_postal, ville, mail, telephone, acces_web, est_eleve)
	VALUES(:prenom, :nom, :rfid, :date_naissance, :date_inscription, :rue, :code_postal, :ville, :mail, :telephone, :acces_web, :est_eleve)');
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
    $new->bindParam(':acces_web', $accesWeb);
    $new->bindParam(':est_eleve', $estEleve);
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