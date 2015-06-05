<?php
/** ADD COURS **/
function addCours(){
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $insertCours = $db->prepare('INSERT INTO cours(intitule, jours, heure_debut, heure_fin, prof_principal, prof_remplacant, niveau, salle, date_debut, date_fin, unite, cout_horaire) VALUES(:intitule, :jours, :heure_debut, :heure_fin, :prof_principal, :prof_remplacant, :niveau, :salle, :date_debut, :date_fin, :unite, :cout_horaire)');
    $insertCours->execute(array(":intitule" => $_POST['intitule'],
                               ":jours" => $_POST['jour'],
                                ":heure_debut" => $_POST['heure_debut'],
                                ":heure_fin" => $_POST['heure_fin'],
                                ":prof_principal" => $_POST['prof_principal'],
                                ":prof_remplacant" => $_POST['prof_remplacant'],
                                ":niveau" => $_POST['niveau'],
                                ":salle" => $_POST['lieu'],
                                ":date_debut" => $_POST['date_debut'],
                                ":date_fin" => $_POST['date_fin'],
                                ":unite" => $_POST['unite'],
                                ":cout_horaire" => $_POST['cout_horaire']));
}
?>