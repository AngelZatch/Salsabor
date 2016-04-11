<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$all_close = $db->query("UPDATE cours SET ouvert = 0");

$update = $db->query("ALTER TABLE produits_adherents
					ADD auto_status TINYINT(1) DEFAULT '1' COMMENT 'Détermine si le système doit changer le statut automatiquement',
					ADD auto_dates TINYINT(1) DEFAULT '1' COMMENT 'Détermine si le système doit changer les dates automatiquement'");
?>
