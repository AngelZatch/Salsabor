<?php
require_once "functions/db_connect.php";
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                /** On obtient le nombre maximum de prestations pour pouvoir afficher plus tard toutes les prestations qui nous correspondent **/
$maxPrestations = $db->query('SELECT COUNT(*) FROM prestations')->fetchColumn();
                $id_liste_tarifs = $db->query('SELECT plages_resa_jour FROM plages_reservations')->fetchAll(PDO::FETCH_COLUMN);
                
                //$liste_jours = array('Semaine', 'Samedi', 'Dimanche');

                /** On construit le tableau à partir de la table plages_reservations **/
                $liste_tarifs = $db->prepare('SELECT * FROM plages_reservations');
                $liste_tarifs->execute();
?>