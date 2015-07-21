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
    
    $queryHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee >= ? AND date_chomee <= ?");
    $queryHoliday->bindParam(1, $_POST["date_activation"]);
    $queryHoliday->bindParam(2, $_POST["date_expiration"]);
    $queryHoliday->execute();
    
    $j = 0;
    
    for($i = 1; $i <= $queryHoliday->rowCount(); $i++){
        $exp_date = date("Y-m-d 00:00:00",strtotime($_POST["date_expiration"].'+'.$i.'DAYS'));
        $checkHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee=?");
        $checkHoliday->bindParam(1, $exp_date);
        $checkHoliday->execute();
        if($checkHoliday->rowCount() != 0){
            $j++;
        }
        $totalOffset = $i + $j;
        $new_exp_date = date("Y-m-d 00:00:00",strtotime($_POST["date_expiration"].'+'.$totalOffset.'DAYS'));
    }
    
    try{
        $db->beginTransaction();
        $new = $db->prepare("INSERT INTO produits_adherents(id_adherent, id_produit, date_achat, date_activation, date_expiration, volume_cours, prix_achat, actif, arep)
        VALUES(:adherent, :produit_id, :date_achat, :date_activation, :date_expiration, :volume_horaire, :prix_achat, :actif, :arep)");
        $new->bindParam(':adherent', $adherent["eleve_id"]);
        $new->bindParam(':produit_id', $_POST["produit"]);
        $new->bindParam(':date_achat', $date_achat);
        $new->bindParam(':date_activation', $_POST["date_activation"]);
        $new->bindParam(':date_expiration', $new_exp_date);
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