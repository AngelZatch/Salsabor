<?php
require_once "db_connect.php";
/** ADD COURS **/
function addCours(){
    $intitule = $_POST['intitule'];
    $weekday = date('N', strtotime($_POST['date_debut']));
    $date_debut = $_POST['date_debut'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $prof_principal = $_POST['prof_principal'];
    $prof_remplacement = $_POST['prof_remplacant'];
    $niveau = $_POST['niveau'];
    $salle = $_POST['lieu'];
    $start = $date_debut." ".$heure_debut;
    $end = $date_debut." ".$heure_fin;
    for($j = 0; $j < 3; $j++){
        if(!isset($_POST['suffixe-'.$j])){
            $_POST['suffixe-'.$j] = 0;
        }
    }
    $suffixe = $_POST['suffixe-0'] + $_POST['suffixe-1'] + $_POST['suffixe-2'];
    $type = $_POST['type'];
    $priorite = 2;
    
    /** Calculs automatiques de valeurs **/
    $unite = (strtotime($heure_fin) - strtotime($heure_debut))/3600;
    $cout_horaire = 40;
	
	$db = PDOFactory::getConnection();
    
    /** Insertion du cours si pas de récurrence (cours ponctuel) **/
    if(!isset($_POST['recurrence'])){
        $recurrence = 0;
        $frequence_repetition = 0;
        $date_fin = $_POST['date_debut'];
        try{
            $db->beginTransaction();
            /** Insertion du cours principal dans cours_parent **/
            $insertCours = $db->prepare('INSERT INTO cours_parent(parent_intitule, weekday, parent_suffixe, parent_type, parent_start_date, parent_end_date, parent_start_time, parent_end_time, parent_prof_principal, parent_prof_remplacant, parent_niveau, parent_salle, parent_unite, parent_cout_horaire, recurrence, frequence_repetition, priorite)
            VALUES(:intitule, :weekday, :suffixe, :type, :date_debut, :date_fin, :heure_debut, :heure_fin, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :recurrence, :frequence_repetition, :priorite)');
            $insertCours->bindParam(':intitule', $intitule);
            $insertCours->bindParam(':weekday', $weekday);
            $insertCours->bindParam(':suffixe', $suffixe);
            $insertCours->bindParam(':type', $type);
            $insertCours->bindParam(':date_debut', $date_debut);
            $insertCours->bindParam(':date_fin', $date_fin);
            $insertCours->bindParam(':heure_debut', $heure_debut);
            $insertCours->bindParam(':heure_fin', $heure_fin);
            $insertCours->bindParam(':prof_principal', $prof_principal);
            $insertCours->bindParam(':prof_remplacant', $prof_remplacement);
            $insertCours->bindParam(':niveau', $niveau);
            $insertCours->bindParam(':lieu', $salle);
            $insertCours->bindParam(':unite', $unite);
            $insertCours->bindParam(':cout_horaire', $cout_horaire);
            $insertCours->bindParam(':recurrence', $recurrence);
            $insertCours->bindParam(':frequence_repetition', $frequence_repetition);
            $insertCours->bindParam(':priorite', $priorite);
            
            $insertCours->execute();
            /** Récupération de l'ID de la dernière insertion dans cours_parent **/
            $last_id = $db->lastInsertId();
            
            /** Insertion du cours principal dans cours **/
            $insertCours = $db->prepare('INSERT INTO cours(cours_parent_id, cours_intitule, cours_suffixe, cours_type, cours_start, cours_end, prof_principal, prof_remplacant, cours_niveau, cours_salle, cours_unite, cours_cout_horaire, priorite)
            VALUES(:cours_parent_id, :intitule, :suffixe, :type, :cours_start, :cours_end, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :priorite)');
            $insertCours->bindParam(':cours_parent_id', $last_id);
            $insertCours->bindParam(':intitule', $intitule);
            $insertCours->bindParam(':suffixe', $suffixe);
            $insertCours->bindParam(':type', $type);
            $insertCours->bindParam(':cours_start', $start);
            $insertCours->bindParam(':cours_end', $end);
            $insertCours->bindParam(':prof_principal', $prof_principal);
            $insertCours->bindParam(':prof_remplacant', $prof_remplacement);
            $insertCours->bindParam(':niveau', $niveau);
            $insertCours->bindParam(':lieu', $salle);
            $insertCours->bindParam(':unite', $unite);
            $insertCours->bindParam(':cout_horaire', $cout_horaire);
            $insertCours->bindParam(':priorite', $priorite);
            
            $insertCours->execute();
            
            $db->commit();
        } catch(PDOException $e){
            $db->rollBack();
            var_dump($e->getMessage());
            }
    } else {
        $recurrence = $_POST['recurrence'];
        $frequence_repetition = $_POST['frequence_repetition'];
        $date_fin = $_POST['date_fin'];
        (int)$nombre_repetitions = (strtotime($date_fin) - strtotime($date_debut))/(86400*$frequence_repetition)+1;
        if($frequence_repetition == 1){
            $weekday = 0;
        }
        try{
            $db->beginTransaction();
            /** Insertion du modèle de cours dans cours_parent **/
            $insertCours = $db->prepare('INSERT INTO cours_parent(parent_intitule, weekday, parent_suffixe, parent_type, parent_start_date, parent_end_date, parent_start_time, parent_end_time, parent_prof_principal, parent_prof_remplacant, parent_niveau, parent_salle, parent_unite, parent_cout_horaire, recurrence, frequence_repetition, priorite)
            VALUES(:intitule, :weekday, :suffixe, :type, :date_debut, :date_fin, :heure_debut, :heure_fin, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :recurrence, :frequence_repetition, :priorite)');
            $insertCours->bindParam(':intitule', $intitule);
            $insertCours->bindParam(':weekday', $weekday);
            $insertCours->bindParam(':suffixe', $suffixe);
            $insertCours->bindParam(':type', $type);
            $insertCours->bindParam(':date_debut', $date_debut);
            $insertCours->bindParam(':date_fin', $date_fin);
            $insertCours->bindParam(':heure_debut', $heure_debut);
            $insertCours->bindParam(':heure_fin', $heure_fin);
            $insertCours->bindParam(':prof_principal', $prof_principal);
            $insertCours->bindParam(':prof_remplacant', $prof_remplacement);
            $insertCours->bindParam(':niveau', $niveau);
            $insertCours->bindParam(':lieu', $salle);
            $insertCours->bindParam(':unite', $unite);
            $insertCours->bindParam(':cout_horaire', $cout_horaire);
            $insertCours->bindParam(':recurrence', $recurrence);
            $insertCours->bindParam(':frequence_repetition', $frequence_repetition);
            $insertCours->bindParam(':priorite', $priorite);
            
            $insertCours->execute();
            /** Récupération de l'ID de la dernière insertion dans cours_parent **/
            $last_id = $db->lastInsertId();
            
            for($i = 1; $i < $nombre_repetitions; $i++){
                /** Insertion de toutes les récurrences du cours dans la table cours **/
                $insertCours = $db->prepare('INSERT INTO cours(cours_parent_id, cours_intitule, cours_suffixe, cours_type, cours_start, cours_end, prof_principal, prof_remplacant, cours_niveau, cours_salle, cours_unite, cours_cout_horaire, priorite)
                VALUES(:cours_parent_id, :intitule, :suffixe, :type, :cours_start, :cours_end, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :priorite)');
                $insertCours->bindParam(':cours_parent_id', $last_id);
                $insertCours->bindParam(':intitule', $intitule);
                $insertCours->bindParam(':suffixe', $suffixe);
                $insertCours->bindParam(':type', $type);
                $insertCours->bindParam(':cours_start', $start);
                $insertCours->bindParam(':cours_end', $end);
                $insertCours->bindParam(':prof_principal', $prof_principal);
                $insertCours->bindParam(':prof_remplacant', $prof_remplacement);
                $insertCours->bindParam(':niveau', $niveau);
                $insertCours->bindParam(':lieu', $salle);
                $insertCours->bindParam(':unite', $unite);
                $insertCours->bindParam(':cout_horaire', $cout_horaire);
                $insertCours->bindParam(':priorite', $priorite);
                $insertCours->execute();
                
                $start_date = strtotime($start.'+'.$frequence_repetition.'DAYS');
                $end_date = strtotime($end.'+'.$frequence_repetition.'DAYS');
                $start = date("Y-m-d H:i", $start_date);
                $end = date("Y-m-d H:i", $end_date);
            }
            $db->commit();
        
        } catch(PDOException $e){
            $db->rollBack();
            var_dump($e->getMessage());
        }
    }
}

/** DELETE COURS **/
function deleteCoursOne(){
    $index = $_POST['id'];
	$db = PDOFactory::getConnection();
    try{
        $db->beginTransaction();
        /** On obtient l'id parent du cours que l'on supprime **/
        $deleteCours = $db->prepare('SELECT cours_parent_id FROM cours WHERE cours_id=?');
        $deleteCours->bindValue(1, $index, PDO::PARAM_INT);
        $deleteCours->execute();
        $parent_id = $deleteCours->fetch(PDO::FETCH_ASSOC);
        /** On supprime le cours **/
        $deleteCours = $db->prepare('DELETE FROM cours WHERE cours_id=?');
        $deleteCours->bindValue(1, $index, PDO::PARAM_INT);
        $deleteCours->execute();
        $db->commit();
        /** On vérifie que le cours parent a encore des enfants **/
        checkParent($parent_id['cours_parent_id']);
    } catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}

function deleteCoursNext(){
    $index = $_POST['id'];
	$db = PDOFactory::getConnection();
    try{
        $db->beginTransaction();
        /** On obtient l'id parent du cours que l'on supprime et on supprime les cours**/
        $findCurrent = $db->prepare('SELECT cours_start, cours_parent_id FROM cours WHERE cours_id=?');
        $findCurrent->bindParam(1, $index, PDO::PARAM_INT);
        $findCurrent->execute();
        $row_findCurrent = $findCurrent->fetch(PDO::FETCH_ASSOC);
        $deleteCours = $db->prepare('DELETE FROM cours WHERE cours_start >=? AND cours_parent_id=?');
        $deleteCours->bindParam(1, $row_findCurrent['cours_start'], PDO::PARAM_STR);
        $deleteCours->bindParam(2, $row_findCurrent['cours_parent_id'], PDO::PARAM_INT);
        $deleteCours->execute();
        $db->commit();
        checkParent($row_findCurrent['cours_parent_id']);
    } catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}

function deleteCoursAll(){
    $index = $_POST['id'];
	$db = PDOFactory::getConnection();
    try{
        $db->beginTransaction();
        $findAll = $db->prepare('SELECT cours_parent_id FROM cours WHERE cours_id=?');
        $findAll->bindParam(1, $index, PDO::PARAM_INT);
        $findAll->execute();
        $row_findAll = $findAll->fetch(PDO::FETCH_ASSOC);
        /** On supprime tous les évènements de la série, caractérisés par le même id parent **/
        $deleteAll = $db->prepare('DELETE FROM cours WHERE cours_parent_id=?');
        $deleteAll->bindParam(1, $row_findAll['cours_parent_id'], PDO::PARAM_INT);
        $deleteAll->execute();
        
        /** On supprime ensuite la référence parent **/
        $deleteAll = $db->prepare('DELETE FROM cours_parent WHERE parent_id=?');
        $deleteAll->bindParam(1, $row_findAll['cours_parent_id'], PDO::PARAM_INT);
        $deleteAll->execute();
        $db->commit();
    } catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}

function checkParent($data){
	$db = PDOFactory::getConnection();
    try{
        $findParent = $db->prepare('SELECT COUNT(*) FROM cours WHERE cours_parent_id=?');
        $findParent->bindParam(1, $data, PDO::PARAM_INT);
        $findParent->execute();
        if($findParent->fetchColumn() == 0){
            $deleteAll = $db->prepare('DELETE FROM cours_parent WHERE parent_id=?');
            $deleteAll->bindParam(1, $data, PDO::PARAM_INT);
            $deleteAll->execute();
        }
    } catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}
?>