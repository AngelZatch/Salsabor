<?php
function getAdherent($prenom, $nom){
	$db = PDOFactory::getConnection();
	$search = $db->prepare('SELECT * FROM adherents WHERE eleve_prenom=? AND eleve_nom=?');
	$search->bindParam(1, $prenom);
	$search->bindParam(2, $nom);
	$search->execute();
	$res = $search->fetch(PDO::FETCH_ASSOC);
	return $res;
}

function getLieu($id){
	$db = PDOFactory::getConnection();
	$search = $db->prepare('SELECT * FROM salle WHERE salle_id=?');
	$search->bindParam(1, $id);
	$search->execute();
	$res = $search->fetch(PDO::FETCH_ASSOC);
	return $res;
}
?>