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

$profs = $db->prepare('SELECT * FROM professeurs WHERE actif=1');
$profs->setFetchMode(PDO::FETCH_ASSOC);
$profs->execute();
$row_profs = $profs->fetchAll();

$niveaux = $db->query('SELECT * FROM niveau');

$lieux = $db->query('SELECT * FROM salle');

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
               <div class="col-sm-9" id="solo-form">
               	<form action="cours_add.php" method="post" class="form-horizontal" role="form">
						 <div class="btn-toolbar">
						   <a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
						   <input type="submit" name="add" role="button" class="btn btn-primary" value="ENREGISTRER">
						</div> <!-- btn-toolbar -->   
						<div class="form-group">
							<label for="intitule" class="col-sm-3 control-label">Intitulé <span class="mandatory">*</span></label>
							<div class="col-sm-9 ui-widget">
								<input type="text" class="form-control" name="intitule" id="cours_tags" placeholder="Nom du cours">
							</div>
					   </div>
					   <div class="form-group">
						   <label for="suffixe" class="col-sm-3 control-label">Suffixe</label>
						   <div class="col-sm-9">
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
					   </div>
					   <div class="form-group">
						   <label for="type" class="col-sm-3 control-label">Type de cours<span class="mandatory">*</span></label>
							<div class="col-sm-9">
								<select name="type" class="form-control">
									<?php while($row_types = $types->fetch(PDO::FETCH_ASSOC)){ ?>
										<option value="<?php echo $row_types['prestations_id'];?>"><?php echo $row_types['prestations_name'];?></option>
									<?php } ?>
								</select>
							</div>
					   </div>
						<div class="form-group">
						   <label for="date_debut" class="col-sm-3 control-label">Date de Début<span class="mandatory">*</span></label>
						   <div class="col-sm-9">
						   <input type="date" class="form-control" name="date_debut" id="date_debut" onChange="checkCalendar(false, false)">
						   </div>
						   <div class="col-sm-9 col-sm-offset-3">
							   <input type="checkbox" name="recurrence" id="recurrence" value="1">Est récurrent
						   </div>
						</div>
						<div class="form-group" id="recurring-options" style="display:none;">
						   <label for="date_fin" class="col-sm-3 control-label">Date de Fin<span class="mandatory">*</span></label>
						   <div class="col-sm-9">
							   <input type="date" class="form-control" name="date_fin" id="date_fin" onChange="checkCalendar(false, true)">
						  </div>
							  <label for="frequence_repetition" class="col-sm-3 control-label">Récurrence<span class="mandatory">*</span></label>
							   <div class="col-sm-9">
								   <div id="options-recurrence">
									   <input type="radio" value="1" name="frequence_repetition" onChange="checkCalendar(false, true)"> Quotidienne<br>
									   <input type="radio" value="7" name="frequence_repetition" onChange="checkCalendar(false, true)"> Hebdomadaire <br>
									   <input type="radio" value="14" name="frequence_repetition" onChange="checkCalendar(false, true)"> Bi-mensuelle<br>
								   </div>
							   </div>
						</div>
						<div class="form-group">
						   <fieldset>
						   <label for="herue_debut" class="col-sm-3 control-label">Début à <span class="mandatory">*</span></label>
						   <div class="col-sm-9">
							   <input type="time" class="form-control hasTimepicker" name="heure_debut" id="heure_debut" onChange="checkCalendar(false, false)">
						   </div>
						   <label for="heure_fin" class="col-sm-3 control-label">Fin à <span class="mandatory">*</span></label>
						   <div class="col-sm-9">
							   <input type="time" class="form-control hasTimepicker" name="heure_fin" id="heure_fin" onChange="checkCalendar(false, false)">
						   </div>
						   </fieldset>
						</div>
						<div class="form-group">
							<fieldset>
							<label for="prof_principal" class="col-sm-3 control-label">Professeur <span class="mandatory">*</span></label>
							<div class="col-sm-9">
							   <select name="prof_principal" class="form-control">
								   <?php foreach ($row_profs as $r){ ?>
										<option value="<?php echo $r['prof_id'];?>"><?php echo $r['prenom']." ".$r['nom'];?></option>
									<?php } ?>
							   </select>
							</div>
							<label for="prof_remplacant" class="col-sm-3 control-label">Remplaçant <span class="mandatory">*</span></label>
							<div class="col-sm-9">
							   <select name="prof_remplacant" class="form-control">
								   <?php foreach ($row_profs as $r){ ?>
										<option value="<?php echo $r['prof_id'];?>"><?php echo $r['prenom']." ".$r['nom'];?></option>
									<?php } ?>
							   </select>
							</div>
							</fieldset>
						</div>
						<div class="form-group">
						   <label for="niveau" class="col-sm-3 control-label">Niveau<span class="mandatory">*</span></label>
							<div class="col-sm-9">
							<select name="niveau" class="form-control">
							<?php while($row_niveaux = $niveaux->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?php echo $row_niveaux['niveau_id'];?>"><?php echo $row_niveaux['niveau_name'];?></option>
							<?php } ?>
							</select>
							</div>
						</div>
						<div class="form-group">
						   <label for="lieu" class="col-sm-3 control-label">Lieu<span class="mandatory">*</span></label>
						   <div class="col-sm-9">
						   <select name="lieu" class="form-control" id="lieu" onChange="checkCalendar(false, false)">
						   <?php while($row_lieux = $lieux->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?php echo $row_lieux['salle_id'];?>"><?php echo $row_lieux['salle_name'];?></option>
							<?php } ?>
							</select>
						   </div>
						</div>
					   <div class="form-group">
						  <div class="col-sm-9 col-sm-offset-3">
							  <label for="paiement" class="control-label">
								  <input type="checkbox" name="paiement" id="paiement" class="checkbox-inline" value="1">Déjà payé<span class="mandatory">*</span>
							  </label>
						  </div>
					   </div>
					   <div class="align-right">
							<p id="error_message"></p>
							<input type="submit" name="add" value="Ajouter" class="btn btn-default btn-primary confirm-add">
					   </div>
					</form>
               </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
   <script>
	$(document).ready(function(){
		var coursNameTags = JSON.parse('<?php echo json_encode($arr_cours_name);?>');
		$('#cours_tags').autocomplete({
			source: coursNameTags
		});
	});
	$("#recurrence").click(function(){
		$("#recurring-options").toggle('600');
	});
	</script>
</body>
</html>