<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
$search = $db->prepare("SELECT * FROM produits_echeances JOIN produits_adherents ON id_produit_adherent=produits_adherents.id_transaction WHERE echeance_effectuee=2 AND id_adherent=?");
$search->bindParam(1, $_POST['search_id']);
$search->execute();
$count = $search->rowCount();
echo $count;
?>