<?php
/** AJOUTER UN TARIF RESERVATION **/
function addTarifResa(){
    $type_prestation = $_POST['type_prestation'];
    $prix_resa = $_POST['prix_resa'];
    /** Lieu réservé et plages horaires récursives et mulitples **/
    
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    try{
        $db->beginTransaction();
        for($k = 1; $k <= 3; $k++){
            if(isset($_POST['jour-'.$k])){
                if(isset($_POST['jour-1'])){
                    for($i = 1; $i <= 3; $i++){
                        if(isset($_POST['plage-'.$i])){
                            $heure_debut_resa = $heures[$i-1];
                            $heure_fin_resa = $heures[$i];
                            $insert = $db->prepare('INSERT INTO tarifs_reservations(type_prestation, jour_resa, heure_debut_resa, heure_fin_resa, lieu_resa, prix_resa)
                            VALUES(:type_prestation, :jour_resa, :heure_debut_resa, :heure_fin_resa, :lieu_resa, :prix_resa)');
                            $insert->bindParam(':type_prestation', $type_prestation);
                            $insert->bindParam(':jour_resa', $_POST['jour-'.$k]);
                            $insert->bindParam(':heure_debut_resa', $heure_debut_resa);
                            $insert->bindParam(':heure_fin_resa', $heure_fin_resa);
                            $insert->bindParam(':lieu_resa', $lieu_resa);
                            $insert->bindParam(':prix_resa', $prix_resa);
                            $insert->execute();
                        }
                    }
                }
            }
        }
        $db->commit();
    }catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}