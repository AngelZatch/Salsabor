<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once "functions/cours.php";

$cours_name = $db->query('SELECT DISTINCT cours_intitule FROM cours');
$arr_cours_name = array();
while($row_cours_name = $cours_name->fetch(PDO::FETCH_ASSOC)){
	array_push($arr_cours_name, trim(preg_replace('/[0-9]+/', '', $row_cours_name['cours_intitule'])));
}

$suffixes = $db->query("SHOW COLUMNS FROM cours_parent LIKE 'parent_suffixe'");

$types = $db->query('SELECT * FROM prestations WHERE est_cours=1');

$queryProfs = $db->query('SELECT * FROM users WHERE est_professeur=1');
$array_profs = array();
while($profs = $queryProfs->fetch(PDO::FETCH_ASSOC)){
	array_push($array_profs, $profs["user_prenom"]." ".$profs["user_nom"]);
}

$niveaux = $db->query('SELECT * FROM niveau');

$lieux = $db->query('SELECT * FROM salle WHERE est_salle_cours=1');

// Ajout d'un cours
if(isset($_POST['add'])){
	addCours();
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Ajouter un cours | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<form method="post" role="form">
					<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
						<legend><span class="glyphicon glyphicon-plus"></span> Ajouter un cours
							<input type="submit" name="add" role="button" class="btn btn-primary" value="ENREGISTRER" id="submit-button" disabled>
						</legend>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="intitule" class="control-label">Intitulé</label>
									<input type="text" class="form-control mandatory input-lg" name="intitule" id="cours_tags" placeholder="Nom du cours">
									<div class="float-right">
										<p id="intitule-error-message" class="error-messages"></p>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="type" class="control-label">Type de cours</label>
							<select name="type" class="form-control mandatory input-lg">
								<?php while($row_types = $types->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?php echo $row_types['prestations_id'];?>"><?php echo $row_types['prestations_name'];?></option>
								<?php } ?>
							</select>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="date_debut" class="control-label">Date de Début</label>
									<div class="input-group input-group-lg">
										<input type="date" class="form-control mandatory" name="date_debut" id="date_debut" onChange="checkCalendar(false, false)">
										<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" date-today="true">Insérer aujourd'hui</a></span>
									</div>
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label for="herue_debut" class="control-label">Début à</label>
									<input type="time" class="form-control hasTimepicker mandatory input-lg" name="heure_debut" id="heure_debut" onChange="checkCalendar(false, false)">
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label for="heure_fin" class="control-label">Fin à</label>
									<input type="time" class="form-control hasTimepicker mandatory input-lg" name="heure_fin" id="heure_fin" onChange="checkCalendar(false, false)">
								</div>
							</div>
						</div>
						<div class="form-group">
							Récurrence <input name="recurrence" id="recurrence" value="0" data-toggle="checkbox-x" data-three-state="false" data-size="lg" value="0">
						</div>
						<div class="form-group" id="recurring-options" style="display:none;">
							<label for="date_fin" class="control-label">Date de Fin</label>
							<input type="date" class="form-control input-lg" name="date_fin" id="date_fin" onChange="checkCalendar(false, true)">
							<label for="frequence_repetition" class="control-label">Récurrence<span class="span-mandatory">*</span></label>
							<div id="options-recurrence">
								<input type="radio" value="1" name="frequence_repetition" onChange="checkCalendar(false, true)"> Quotidienne<br>
								<input type="radio" value="7" name="frequence_repetition" onChange="checkCalendar(false, true)"> Hebdomadaire <br>
								<input type="radio" value="14" name="frequence_repetition" onChange="checkCalendar(false, true)"> Bi-mensuelle<br>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="prof_principal" class="control-label">Professeur principal</label>
									<input type="text" name="prof_principal" class="form-control mandatory has-name-completion input-lg">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label for="prof_remplacant" class="control-label">Professeur remplaçant</label>
									<input type="text" name="prof_remplacant" class="form-control has-name-completion input-lg">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="niveau" class="control-label">Niveau</label>
									<select name="niveau" class="form-control mandatory input-lg">
										<?php while($row_niveaux = $niveaux->fetch(PDO::FETCH_ASSOC)){ ?>
										<option value="<?php echo $row_niveaux['niveau_id'];?>"><?php echo $row_niveaux['niveau_name'];?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label for="lieu" class="control-label">Lieu</label>
									<select name="lieu" class="form-control mandatory input-lg" id="lieu" onChange="checkCalendar(false, false)">
										<?php while($row_lieux = $lieux->fetch(PDO::FETCH_ASSOC)){ ?>
										<option value="<?php echo $row_lieux['salle_id'];?>"><?php echo $row_lieux['salle_name'];?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="paiement" class="control-label">
								<input type="checkbox" name="paiement" id="paiement" class="checkbox-inline mandatory" value="1">Déjà payé <span class="span-mandatory">*</span>
							</label>
						</div>
						<div class="align-right">
							<p id="error_message"></p>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/check_calendar.js"></script>
		<script>
			$(document).ready(function(){
				var coursNameTags = JSON.parse('<?php echo json_encode($arr_cours_name);?>');
				var listeProfs = JSON.parse('<?php echo json_encode($array_profs);?>');
				$('#cours_tags').autocomplete({
					source: coursNameTags
				});
				$(".has-name-completion").autocomplete({
					source: listeProfs
				})
				var start = sessionStorage.getItem('start');
				if(start != null){
					var format_start = new Date(start).toISOString();
					var end = sessionStorage.getItem('end');
					var format_end = new Date(end).toISOString();
					var start_hour = moment(format_start).format('HH:mm');
					var end_hour = moment(format_end).format('HH:mm');
				} else {
					var format_start = new Date().toISOString();
					var format_end = new Date().toISOString();
					var start_hour = moment(format_start).startOf('hour').add(1, 'h').format('HH:mm');
					var end_hour = moment(format_end).startOf('hour').add(2, 'h').format('HH:mm');
				}
				var start_day = moment(format_start).format('YYYY-MM-DD');

				$("#date_debut").val(start_day);
				$("#heure_debut").val(start_hour);
				$("#heure_fin").val(end_hour);

				sessionStorage.removeItem('end');
				sessionStorage.removeItem('start');
			});
			$("#recurrence").change(function(){
				$("#recurring-options").toggle('600');
			});
		</script>
	</body>
</html>
