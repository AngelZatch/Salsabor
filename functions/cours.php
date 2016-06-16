<?php
require_once "db_connect.php";
require_once "tools.php";
/** ADD COURS **/
function addCours(){
	$db = PDOFactory::getConnection();

	$intitule = $_POST['intitule'];
	$weekday = date('N', strtotime($_POST['date_debut']));
	$date_debut = $_POST['date_debut'];
	$heure_debut = $_POST['heure_debut'];
	$heure_fin = $_POST['heure_fin'];
	$prof_principal = solveAdherentToId($_POST["prof_principal"]);
	$prof_remplacement = solveAdherentToId($_POST["prof_remplacant"]);
	$niveau = $_POST['niveau'];
	$salle = $_POST['lieu'];
	$start = $date_debut." ".$heure_debut;
	$end = $date_debut." ".$heure_fin;
	$type = $_POST['type'];
	$priorite = 2;

	/** Calculs automatiques de valeurs **/
	$unite = (strtotime($heure_fin) - strtotime($heure_debut))/3600;

	$prof = $db->query("SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=$prof_principal AND type_prestation=$type")->fetch(PDO::FETCH_ASSOC);
	if($prof["ratio_multiplicatif"] == "heure"){
		$cout_horaire = $unite * $prof["tarif_prestation"];
	} else if($prof["ratio_multiplicatif"] == "prestation"){
		$cout_horaire = $prof["tarif_prestation"];
	} else {
		$cout_horaire = 0;
	}

	if(isset($_POST['paiement'])) $paiement = $_POST['paiement']; else $paiement = 0;

	/** Insertion du cours si pas de récurrence (cours ponctuel) **/
	if($_POST['recurrence'] == 0){
		$recurrence = 0;
		$frequence_repetition = 0;
		$date_fin = $_POST['date_debut'];
		try{
			$db->beginTransaction();
			/** Insertion du cours principal dans cours_parent **/
			$insertCours = $db->prepare('INSERT INTO cours_parent(parent_intitule, weekday, parent_type, parent_start_date, parent_end_date, parent_start_time, parent_end_time, parent_prof_principal, parent_prof_remplacant, parent_niveau, parent_salle, parent_unite, parent_cout_horaire, recurrence, frequence_repetition, priorite)
			VALUES(:intitule, :weekday, :type, :date_debut, :date_fin, :heure_debut, :heure_fin, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :recurrence, :frequence_repetition, :priorite)');
			$insertCours->bindParam(':intitule', $intitule);
			$insertCours->bindParam(':weekday', $weekday);
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
			$insertCours = $db->prepare('INSERT INTO cours(cours_parent_id, cours_intitule, cours_type, cours_start, cours_end, prof_principal, prof_remplacant, cours_niveau, cours_salle, cours_unite, cours_prix, priorite, paiement_effectue)
			VALUES(:cours_parent_id, :intitule, :type, :cours_start, :cours_end, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :priorite, :paiement)');
			$insertCours->bindParam(':cours_parent_id', $last_id);
			$insertCours->bindParam(':intitule', $intitule);
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
			$insertCours->bindParam(':paiement', $paiement);

			$insertCours->execute();
			$db->commit();
			header("Location: planning");
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
			$insertCours = $db->prepare('INSERT INTO cours_parent(parent_intitule, weekday, parent_type, parent_start_date, parent_end_date, parent_start_time, parent_end_time, parent_prof_principal, parent_prof_remplacant, parent_niveau, parent_salle, parent_unite, parent_cout_horaire, recurrence, frequence_repetition, priorite)
			VALUES(:intitule, :weekday, :type, :date_debut, :date_fin, :heure_debut, :heure_fin, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :recurrence, :frequence_repetition, :priorite)');
			$insertCours->bindParam(':intitule', $intitule);
			$insertCours->bindParam(':weekday', $weekday);
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
				$insertCours = $db->prepare('INSERT INTO cours(cours_parent_id, cours_intitule, cours_type, cours_start, cours_end, prof_principal, prof_remplacant, cours_niveau, cours_salle, cours_unite, cours_prix, priorite, paiement_effectue)
				VALUES(:cours_parent_id, :intitule, :type, :cours_start, :cours_end, :prof_principal, :prof_remplacant, :niveau, :lieu, :unite, :cout_horaire, :priorite, :paiement)');
				$insertCours->bindParam(':cours_parent_id', $last_id);
				$insertCours->bindParam(':intitule', $intitule);
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
				$insertCours->bindParam(':paiement', $paiement);
				$insertCours->execute();

				$start_date = strtotime($start.'+'.$frequence_repetition.'DAYS');
				$end_date = strtotime($end.'+'.$frequence_repetition.'DAYS');
				$start = date("Y-m-d H:i", $start_date);
				$end = date("Y-m-d H:i", $end_date);
			}
			$db->commit();
			header("Location: planning");
		} catch(PDOException $e){
			$db->rollBack();
			var_dump($e->getMessage());
		}
	}
}
?>
