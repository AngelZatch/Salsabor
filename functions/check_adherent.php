<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
$search = $db->prepare('SELECT * FROM adherents WHERE eleve_prenom=? AND eleve_nom=?');
$search->bindParam(1, $_POST['identite_prenom']);
$search->bindParam(2, $_POST['identite_nom']);
$search->execute();
$count = $search->rowCount();
echo $count;
?>