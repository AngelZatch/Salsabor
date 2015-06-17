<?php
function estimatePrix(){
    $prestation = $_POST['prestation'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $lieu = $_POST['lieu'];
    
    
    /** Conversion de la date **/
    $date = date_create($_POST['date_resa'])->format('N');
    if($date <= 5){
        $plage_resa = 1;
    }
    else if($date == 6){
        $plage_resa = 2;
    }
    else $plage_resa = 3;
    
    $findHours = $db->prepare('SELECT tarif_resa_id, prix_resa FROM tarifs_reservation JOIN plages_reservations ON (plage_resa=plages_reservations.plages_resa_id) WHERE type_prestation=? AND plage_resa_jour=? AND heure_debut<? AND heure_fin>? AND lieu_resa=?');
    $findHours->bindValue(1, $prestation);
    $findHours->bindValue(2, $plage_resa);
    $findHours->bindValue(3, $heure_debut);
    $findHours->bindValue(4, $heure_fin);
    $findHours->bindValue(5, $lieu);
    $findHours->execute();
    $res = $findHours->fetch(PDO::FETCH_ASSOC);
    return $res;
}
?>