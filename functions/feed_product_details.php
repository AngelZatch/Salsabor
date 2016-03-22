<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["id"];

$feed = $db->prepare("SELECT * FROM produits WHERE produit_id = ?");
$feed->bindValue(1, $id);
$feed->execute();
echo json_encode($feed->fetch(PDO::FETCH_ASSOC));
?>
