<?php
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
    
    /** Calculs automatiques de valeurs **/
    $unite = (strtotime($heure_fin) - strtotime($heure_debut))/3600;
    $cout_horaire = 40;
    
    /** Insertion du cours si pas de récurrence (cours ponctuel) **/
    if(!isset($_POST['recurrence'])){
        $recurrence = 0;
        $frequence_repetition = 0;
        $date_fin = $_POST['date_debut'];
        $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try{
            $db->beginTransaction();
            /** Insertion du cours principal dans cours_parent **/
            $insertCours = $db->prepare('INSERT INTO cours_parent(parent_intitule, weekday, parent_start_date, parent_end_date, parent_start_time, parent_end_time, parent_prof_principal, parent_prof_remplacant, parent_niveau, parent_salle, parent_unite, parent_cout_horaire, recurrence, frequence_repetition)
            VALUES(:intitule, :weekday, :date_debut, :date_fin, :heure_debut, :heure_fin, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :recurrence, :frequence_repetition)');
            $insertCours->bindParam(':intitule', $intitule);
            $insertCours->bindParam(':weekday', $weekday);
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
            
            $insertCours->execute();
            /** Récupération de l'ID de la dernière insertion dans cours_parent **/
            $last_id = $db->lastInsertId();
            
            /** Insertion du cours principal dans cours **/
            $insertCours = $db->prepare('INSERT INTO cours(cours_parent_id, cours_intitule, cours_start, cours_end, prof_principal, prof_remplacant, cours_niveau, cours_salle, cours_unite, cours_cout_horaire)
            VALUES(:cours_parent_id, :intitule, :cours_start, :cours_end, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire)');
            $insertCours->bindParam(':cours_parent_id', $last_id);
            $insertCours->bindParam(':intitule', $intitule);
            $insertCours->bindParam(':cours_start', $start);
            $insertCours->bindParam(':cours_end', $end);
            $insertCours->bindParam(':prof_principal', $prof_principal);
            $insertCours->bindParam(':prof_remplacant', $prof_remplacement);
            $insertCours->bindParam(':niveau', $niveau);
            $insertCours->bindParam(':lieu', $salle);
            $insertCours->bindParam(':unite', $unite);
            $insertCours->bindParam(':cout_horaire', $cout_horaire);
            
            $insertCours->execute();
            
            $db->commit();
        } catch(PDOException $e){
            $db->rollBack();
            var_dump($e->getMessage());
        }

    } else {
        $recurrence = $_POST['recurrence'];
        $frequence_repetition = $_POST['frequence_repetition'];
        $until = (365/$frequence_repetition);
        if($frequence_repetition == 1){
            $weekday = 0;
        }
        $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->beginTransaction();
        try{
            $insertCours = $db->prepare('INSERT INTO cours_parent(parent_intitule, weekday, parent_start_date, parent_end_date, parent_start_time, parent_end_time, parent_prof_principal, parent_prof_remplacant, parent_niveau, parent_salle, parent_unite, parent_cout_horaire, recurrence, frequence_repetition)
            VALUES(:intitule, :weekday, :date_debut, :date_fin, :heure_debut, :heure_fin, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :recurrence, :frequence_repetition)');
            $insertCours->bindParam(':intitule', $intitule);
            $insertCours->bindParam(':weekday', $weekday);
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
            
            $insertCours->execute();
            /** Récupération de l'ID de la dernière insertion dans cours_parent **/
            $last_id = $db->lastInsertId();
            
            for($x = 0; $x < $until; $x++){
                /** Insertion du cours principal dans cours **/
                $insertCours = $db->prepare('INSERT INTO cours(cours_parent_id, cours_intitule, cours_start, cours_end, prof_principal, prof_remplacant, cours_niveau, cours_salle, cours_unite, cours_cout_horaire)
                VALUES(:cours_parent_id, :intitule, :cours_start, :cours_end, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire)');
                $insertCours->bindParam(':cours_parent_id', $last_id);
                $insertCours->bindParam(':intitule', $intitule);
                $insertCours->bindParam(':cours_start', $start);
                $insertCours->bindParam(':cours_end', $end);
                $insertCours->bindParam(':prof_principal', $prof_principal);
                $insertCours->bindParam(':prof_remplacant', $prof_remplacement);
                $insertCours->bindParam(':niveau', $niveau);
                $insertCours->bindParam(':lieu', $salle);
                $insertCours->bindParam(':unite', $unite);
                $insertCours->bindParam(':cout_horaire', $cout_horaire);
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
?>