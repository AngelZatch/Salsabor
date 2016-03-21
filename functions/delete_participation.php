<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$participation_id = $_POST["record_id"];

$load = $db->query("SELECT produit_adherent_id FROM cours_participants WHERE id = '$participation_id'")->fetch(PDO::FETCH_ASSOC);

$assign = $db->query("DELETE FROM cours_participants WHERE id='$participation_id'");

echo $load["produit_adherent_id"];
?>
