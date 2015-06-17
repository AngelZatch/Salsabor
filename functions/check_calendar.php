<?php
require_once "../functions/db_connect.php";
// Vérifie si l'emplacement est libre
$heure_debut = $_POST['heure_debut'];
$heure_fin = $_POST['heure_fin'];
$lieu = $_POST['lieu'];

/** Conversion de la date **/
$date = $_POST['date_resa']." ".$_POST['heure_debut'];

$findResa = $db->prepare('SELECT * FROM cours WHERE cours_salle=? AND cours_start=?');
$findResa->bindValue(1, $lieu);
$findResa->bindValue(2, $date);
$findResa->execute();
echo $res = $findResa->fetchColumn();
?>