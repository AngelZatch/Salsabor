<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$record_id = $_POST["record_id"];

$load = $db->query("SELECT produit_adherent_id FROM cours_participants WHERE id = '$record_id'")->fetch(PDO::FETCH_ASSOC);

$assign = $db->query("DELETE FROM cours_participants WHERE id='$record_id'");

echo $load["produit_adherent_id"];
?>
