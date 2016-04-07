<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$participation_id = $_POST["participation_id"];

$load = $db->query("SELECT * FROM cours_participants WHERE id = '$participation_id'")->fetch(PDO::FETCH_ASSOC);

$session_id = $load["cours_id_foreign"];
$user_id = $load["eleve_id_foreign"];

$assign = $db->query("UPDATE cours_participants SET produit_adherent_id = NULL WHERE id='$participation_id'");
$updateRecord = $db->query("UPDATE passages SET produit_adherent_cible = NULL WHERE passage_eleve_id='$user_id' AND cours_id='$session_id'");

echo $load["produit_adherent_id"];
?>
