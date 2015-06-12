<?php
/** AJOUTER UN TARIF RESERVATION **/
function addTarifResa(){
    $type_prestation = $_POST['type_prestation'];
    $heure_debut_resa = $_POST['heure_debut'];
    $heure_fin_resa = $_POST['heure_fin'];
    $lieu_resa = $_POST['lieu_resa'];
    $prix_resa = $_POST['prix_resa'];
    
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    try{
        $db->beginTransaction();
        for($k = 1; $k <= 3; $k++){
            if(isset($_POST['jour-'.$k])){
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
        $db->commit();
    }catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}