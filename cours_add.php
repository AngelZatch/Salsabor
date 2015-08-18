<?php
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

$profs = $db->prepare('SELECT * FROM users WHERE est_professeur=1');
$profs->setFetchMode(PDO::FETCH_ASSOC);
$profs->execute();
$row_profs = $profs->fetchAll();

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
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-plus"></span> Ajouter un cours</h1>
               <form action="cours_add.php" method="post" role="form">
				   <div class="btn-toolbar">
					   <a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
					   <input type="submit" name="add" role="button" class="btn btn-primary" value="ENREGISTRER" onClick="checkMandatory()" id="submit-button" disabled>
               						</div> <!-- btn-toolbar -->   
					<div class="form-group">
						<label for="intitule" class="control-label">Intitulé <span class="span-mandatory">*</span></label>
						<div class="ui-widget">
							<input type="text" class="form-control mandatory" name="intitule" id="cours_tags" placeholder="Nom du cours">
							<div class="float-right">
								<p id="intitule-error-message" class="error-messages"></p>
							</div>
						</div>
				   </div>
				   <div class="form-group">
					   <label for="suffixe" class="control-label">Suffixe</label>
					   <?php
						while ($row_suffixes = $suffixes->fetch(PDO::FETCH_ASSOC)){
							$array_suffixes = preg_split("/','/", substr($row_suffixes['Type'], 5, strlen($row_suffixes['Type'])-7));
							$j = 1;
							for($i = 0; $i < 3; $i++){?>
							<input type="checkbox" name="suffixe<?php echo $i;?>" id="suffixe-<?php echo $i;?>" class="checkbox-inline" value="<?php echo $j;?>"><?php echo $array_suffixes[$i];?>
							<?php $j *= 2;
							}
						}
						?>
				   </div>
				   <div class="form-group">
					   <label for="type" class="control-label">Type de cours</label>
						<select name="type" class="form-control mandatory">
							<?php while($row_types = $types->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?php echo $row_types['prestations_id'];?>"><?php echo $row_types['prestations_name'];?></option>
							<?php } ?>
						</select>
				   </div>
					<div class="form-group">
					   <label for="date_debut" class="control-label">Date de Début</label>
					   <div class="input-group">
						   <input type="date" class="form-control mandatory" name="date_debut" id="date_debut" onChange="checkCalendar(false, false)">
						  <span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" date-today="true">Insérer aujourd'hui</a></span>
					   </div>
					   <input type="checkbox" name="recurrence" id="recurrence" value="1">Est récurrent
					</div>
					<div class="form-group" id="recurring-options" style="display:none;">
					   <label for="date_fin" class="control-label">Date de Fin</label>
					   <input type="date" class="form-control" name="date_fin" id="date_fin" onChange="checkCalendar(false, true)">
						  <label for="frequence_repetition" class="control-label">Récurrence<span class="span-mandatory">*</span></label>
						   <div id="options-recurrence">
							   <input type="radio" value="1" name="frequence_repetition" onChange="checkCalendar(false, true)"> Quotidienne<br>
							   <input type="radio" value="7" name="frequence_repetition" onChange="checkCalendar(false, true)"> Hebdomadaire <br>
							   <input type="radio" value="14" name="frequence_repetition" onChange="checkCalendar(false, true)"> Bi-mensuelle<br>
						   </div>
					</div>
					<div class="form-group">
					   <label for="herue_debut" class="control-label">Début à</label>
					   <input type="time" class="form-control hasTimepicker mandatory" name="heure_debut" id="heure_debut" onChange="checkCalendar(false, false)">
				   </div>
				   <div class="form-group">
					   <label for="heure_fin" class="control-label">Fin à</label>
					   <input type="time" class="form-control hasTimepicker mandatory" name="heure_fin" id="heure_fin" onChange="checkCalendar(false, false)">
					</div>
					<div class="form-group">
						<label for="prof_principal" class="control-label">Professeur principal</label>
						<select name="prof_principal" class="form-control mandatory">
						<?php foreach ($row_profs as $r){ ?>
							<option value="<?php echo $r['user_id'];?>"><?php echo $r['user_prenom']." ".$r['user_nom'];?></option>
						<?php } ?>
						</select>
					</div>
					<div class="form-group">
						<label for="prof_remplacant" class="control-label">Professeur remplaçant</label>
						<select name="prof_remplacant" class="form-control mandatory">
						<?php foreach ($row_profs as $r){ ?>
							<option value="<?php echo $r['user_id'];?>"><?php echo $r['user_prenom']." ".$r['user_nom'];?></option>
						<?php } ?>
						</select>
					</div>
					<div class="form-group">
					   <label for="niveau" class="control-label">Niveau</label>
						<select name="niveau" class="form-control mandatory">
						<?php while($row_niveaux = $niveaux->fetch(PDO::FETCH_ASSOC)){ ?>
							<option value="<?php echo $row_niveaux['niveau_id'];?>"><?php echo $row_niveaux['niveau_name'];?></option>
						<?php } ?>
						</select>
					</div>
					<div class="form-group">
					   <label for="lieu" class="control-label">Lieu</label>
					   <select name="lieu" class="form-control mandatory" id="lieu" onChange="checkCalendar(false, false)">
					   <?php while($row_lieux = $lieux->fetch(PDO::FETCH_ASSOC)){ ?>
							<option value="<?php echo $row_lieux['salle_id'];?>"><?php echo $row_lieux['salle_name'];?></option>
						<?php } ?>
						</select>
					</div>
				   <div class="form-group">
					  <label for="paiement" class="control-label">
					  <input type="checkbox" name="paiement" id="paiement" class="checkbox-inline mandatory" value="1">Déjà payé <span class="span-mandatory">*</span>
					  </label>
				   </div>
				   <div class="align-right">
						<p id="error_message"></p>
				   </div>
				</form>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script src="assets/js/check_calendar.js"></script>  
   <script>
	$(document).ready(function(){
		var coursNameTags = JSON.parse('<?php echo json_encode($arr_cours_name);?>');
		$('#cours_tags').autocomplete({
			source: coursNameTags
		});
       	var start = sessionStorage.getItem('start');
		if(start != null){
			var format_start = new Date(start).toISOString();
			var end = sessionStorage.getItem('end');
			var format_end = new Date(end).toISOString();
			var start_hour = moment(format_start).format('HH:mm');
			var end_hour = moment(format_end).format('HH:mm');
		} else {
			var format_start = new Date().toISOString();
			console.log(format_start);
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
	$("#recurrence").click(function(){
		$("#recurring-options").toggle('600');
	});
	</script>
</body>
</html>