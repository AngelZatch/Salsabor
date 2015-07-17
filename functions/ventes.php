<?php
require_once "db_connect.php";
include "librairies/fpdf/fpdf.php";
include "librairies/fpdi/fpdi.php";
require_once "tools.php";

function vente(){
    $db = PDOFactory::getConnection();
    $prenom = $_POST["identite_prenom"];
    $nom = $_POST["identite_nom"];
    $adherent = getAdherent($prenom, $nom);
    
    $date_achat = date_create("now")->format('Y-m-d H:i:s');
    
    $actif = ($_POST["date_activation"]>$date_achat)?0:1;
    
    try{
        $db->beginTransaction();
        $new = $db->prepare("INSERT INTO produits_adherents(id_adherent, id_produit, date_achat, date_activation, date_expiration, volume_cours, prix_achat, actif, arep)
        VALUES(:adherent, :produit_id, :date_achat, :date_activation, :date_expiration, :volume_horaire, :prix_achat, :actif, :arep)");
        $new->bindParam(':adherent', $adherent["eleve_id"]);
        $new->bindParam(':produit_id', $_POST["produit"]);
        $new->bindParam(':date_achat', $date_achat);
        $new->bindParam(':date_activation', $_POST["date_activation"]);
        $new->bindParam(':date_expiration', $_POST["date_expiration"]);
        $new->bindParam(':volume_horaire', $_POST["volume_horaire"]);
        $new->bindParam(':prix_achat', $_POST["prix_achat"]);
        $new->bindParam(':actif', $actif);
        $new->bindParam(':arep', $_POST["autorisation_report"]);
        $new->execute();
        $db->commit();
    }catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}

?>