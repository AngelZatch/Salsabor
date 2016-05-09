<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once 'functions/cours.php';
/** Récupération des valeurs dans la base de données des champs **/
$id = $_GET['id'];
$queryCours = $db->prepare('SELECT * FROM cours WHERE cours_id=?');
$queryCours->bindParam(1, $id);
$queryCours->execute();
$cours = $queryCours->fetch(PDO::FETCH_ASSOC);

$queryParent = $db->prepare('SELECT recurrence, frequence_repetition, parent_end_date FROM cours_parent WHERE parent_id=?');
$queryParent->bindParam(1, $cours['cours_parent_id']);
$queryParent->execute();
$res_recurrence = $queryParent->fetch(PDO::FETCH_ASSOC);

$queryProf = $db->prepare('SELECT * FROM users WHERE user_id=? AND est_professeur=1');
$queryProf->bindParam(1, $cours['prof_principal']);
$queryProf->execute();
$data_prof = $queryProf->fetch(PDO::FETCH_ASSOC);

/*$queryTarif = $db->prepare('SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=? AND type_prestation=?');
$queryTarif->bindParam(1, $cours['prof_principal']);
$queryTarif->bindParam(2, $cours['cours_type']);
$queryTarif->execute();
$tarif = $queryTarif->fetch(PDO::FETCH_ASSOC);*/

$queryTypes = $db->query('SELECT * FROM prestations WHERE est_cours=1');

$querySalles = $db->query("SELECT * FROM salle WHERE est_salle_cours=1");

if(isset($_POST['editOne'])){
	$db = PDOFactory::getConnection();
	$start = $_POST['date_debut']." ".$_POST['heure_debut'];
	$end = $_POST['date_debut']." ".$_POST['heure_fin'];
	$paiement = $_POST['paiement'];
	$prix_final = $_POST['prix_cours'];
	$cours_type = $_POST["type"];
	$cours_salle = $_POST["salle"];
	try{
		$db->beginTransaction();
		$edit = $db->prepare('UPDATE cours SET cours_intitule = :intitule,
										cours_start = :cours_start,
										cours_end = :cours_end,
										cours_prix = :prix,
										cours_type = :cours_type,
										cours_salle = :cours_salle,
										paiement_effectue = :paiement,
										justification_modification = :edit_comment,
										derniere_modification = :derniere_modification
										WHERE cours_id = :id');
		$edit->bindParam(':intitule', $_POST['intitule']);
		$edit->bindParam(':cours_start', $start);
		$edit->bindParam(':cours_end', $end);
		$edit->bindParam(':cours_type', $cours_type);
		$edit->bindParam(':cours_salle', $cours_salle);
		$edit->bindParam(':prix', $prix_final);
		$edit->bindParam(':paiement', $paiement);
		$edit->bindParam(':edit_comment', $_POST['edit-comment']);
		$edit->bindParam(':derniere_modification', date_create('now')->format('Y-m-d H:i:s'));
		$edit->bindParam(':id', $id);
		$edit->execute();
		$db->commit();
	} catch (PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
	header('Location: ../planning');
}

if(isset($_POST['editNext'])){
	/** Edition de tous les suivants. Il faut donc rétablir la récurrence en cas de changement de dates **/
	$start = $_POST['date_debut']." ".$_POST['heure_debut'];
	$end = $_POST['date_debut']." ".$_POST['heure_fin'];
	$frequence_repetition = $res_recurrence['frequence_repetition'];
	$date_fin = $_POST['parent_end_date'];
	(int)$nombre_repetitions = (strtotime($res_recurrence['parent_end_date']) - strtotime($_POST['date_debut']))/(86400*$frequence_repetition)+1;
	$paiement = $_POST['paiement'];
	$prix_final = $_POST['prix_cours'];

	$db = PDOFactory::getConnection();
	try{
		$db->beginTransaction();
		for($i = 1; $i < $nombre_repetitions; $i++){
			$edit = $db->prepare('UPDATE cours SET cours_intitule = :intitule,
											cours_start = :cours_start,
											cours_end = :cours_end,
											cours_prix = :prix,
											justification_modification = :edit_comment,
											paiement_effectue = :paiement
							WHERE cours_parent_id = :parent_id AND cours_id = :id');
			$edit->bindParam(':intitule', $_POST['intitule']);
			$edit->bindParam(':cours_start', $start);
			$edit->bindParam(':cours_end', $end);
			$edit->bindParam(':prix', $prix_final);
			$edit->bindParam(':edit_comment', $_POST['edit-comment']);
			$edit->bindParam(':paiement', $_POST['paiement']);
			$edit->bindParam(':parent_id', $cours['cours_parent_id']);
			$edit->bindParam(':id', $id);
			$edit->execute();

			$start_date = strtotime($start.'+'.$frequence_repetition.'DAYS');
			$end_date = strtotime($end.'+'.$frequence_repetition.'DAYS');
			$start = date("Y-m-d H:i", $start_date);
			$end = date("Y-m-d H:i", $end_date);
			$id++;
		}
		$db->commit();
	} catch (PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
	header('Location: ../planning');
}

if(isset($_POST['editAll'])){

}

// Sauf d'un seul cours
if(isset($_POST['deleteCoursOne'])){
	deleteCoursOne();
}

// Suppression de tous les cours suivant le sélectionné
if(isset($_POST['deleteCoursNext'])){
	deleteCoursNext();
}

// Suppression de tous les cours du même genre que le sélectionné
if(isset($_POST['deleteCoursAll'])){
	deleteCoursAll();
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Cours de <?php echo $cours['cours_intitule'];?> (<?php echo date_create($cours['cours_start'])->format('d/m/Y');?> : <?php echo date_create($cours['cours_start'])->format('H:i')?> / <?php echo date_create($cours['cours_end'])->format('H:i');?>) | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script src="assets/js/participations.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<form method="post" role="form" class="form-horizontal">
					<div class="fixed">
						<div class="col-lg-6">
							<p class="page-title"><span class="glyphicon glyphicon-eye-open"></span> Cours de <?php echo $cours['cours_intitule'];?></p>
						</div>
						<div class="col-lg-6">
							<div class="btn-toolbar">
								<?php if($res_recurrence == '0'){ ?>
								<input type='submit' name='editOne' role='button' class='btn btn-success' value='ENREGISTRER'>
								<?php } else { ?>
								<a href='#save-options' class='btn btn-primary' role='button' data-toggle='collapse' aria-expanded='false' aria-controls='saveOptions'>ENREGISTRER</a>
								<?php } ?>
								<a href="#delete-options" role="button" class="btn btn-danger" data-toggle="collapse" aria-expanded="false" aria-controls="deleteOptions"><span class="glyphicon glyphicon-trash"></span> Supprimer</a>
								<input type="hidden" name="id" value="<?php echo $id;?>">
								<div class="collapse" id="save-options">
									<div class="well">
										<input type="submit" name="editOne" role="button" class="btn btn-success" value="Cet évènement">
										<input type="submit" name="editNext" role="button" class="btn btn-success" value="Tous les suivants">
										<button class="btn btn-primary">Toute la série</button>
									</div>
								</div>
								<div class="collapse" id="delete-options">
									<div class="well">
										<input type="submit" name="deleteCoursOne" role="button" class="btn btn-danger" value="Cet évènement">
										<input type="submit" name="deleteCoursNext" role="button" class="btn btn-danger" value="Tous les suivants">
										<input type="submit" name="deleteCoursAll" role="button" class="btn btn-danger" value="Toute la série">
									</div>
								</div>
							</div> <!-- btn-toolbar -->
						</div>
					</div>
					<div class="col-lg-10 col-lg-offset-2 main">
						<p id="last-edit"><?php if($cours['derniere_modification'] != '0000-00-00 00:00:00') echo "Dernière modification le ".date_create($cours['derniere_modification'])->format('d/m/Y')." à ".date_create($cours['derniere_modification'])->format('H:i');?></p>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Intitulé du cours</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="intitule" value="<?php echo $cours['cours_intitule'];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Professeur</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="enseignant" id="enseignant" value="<?php echo $data_prof['user_prenom']." ".$data_prof['user_nom'];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Date</label>
							<div class="col-lg-9">
								<input type="date" class="form-control" name="date_debut" id="date_debut" value="<?php echo date_create($cours['cours_start'])->format('Y-m-d');?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Heure de début</label>
							<div class="col-lg-9">
								<input type="time" class="form-control" name="heure_debut" id="heure_debut" value="<?php echo date_create($cours['cours_start'])->format('H:i')?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Heure de fin</label>
							<div class="col-lg-9">
								<input type="time" class="form-control" name="heure_fin" id="heure_fin" value="<?php echo date_create($cours['cours_end'])->format('H:i');?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Type de cours</label>
							<div class="col-lg-9">
								<select name="type" class="form-control">
									<?php while($types = $queryTypes->fetch(PDO::FETCH_ASSOC)){
	if($cours["cours_type"] == $types["prestations_id"]) { ?>
									<option selected="selected" value="<?php echo $types['prestations_id'];?>"><?php echo $types['prestations_name'];?></option>;
									<?php } else { ?>
									<option value="<?php echo $types['prestations_id'];?>"><?php echo $types['prestations_name'];?></option>;
									<?php }
} ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Salle</label>
							<div class="col-lg-9">
								<select name="salle" class="form-control">
									<?php while($salles = $querySalles->fetch(PDO::FETCH_ASSOC)){
	if($cours["cours_salle"] == $salles["salle_id"]) {?>
									<option selected="selected" value="<?php echo $salles["salle_id"];?>"><?php echo $salles["salle_name"];?></option>
									<?php } else { ?>
									<option value="<?php echo $salles["salle_id"];?>"><?php echo $salles["salle_name"];?></option>
									<?php }
} ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Commentaires</label>
							<div class="col-lg-9">
								<textarea name="edit-comment" id="edit-comment" cols="30" rows="5" class="form-control"></textarea>
							</div>
						</div>
						<div class="panel panel-session" id="session-<?php echo $id;?>">
							<a class="panel-heading-container" id='ph-session-<?php echo $id;?>' data-session='<?php echo $id;?>' data-trigger='<?php echo $id;?>'>
								<div class="panel-heading">
									<div class="container-fluid">
										<p class="col-md-3">Liste des participants</p>
										<p class="col-lg-1"><span class="glyphicon glyphicon-user"></span> <span class="user-total-count" id="user-total-count-<?php echo $id;?>"></span></p>
										<p class="col-lg-1"><span class="glyphicon glyphicon-ok"></span> <span class="user-ok-count" id="user-ok-count-<?php echo $id;?>"></span></p>
										<p class="col-lg-1"><span class="glyphicon glyphicon-warning-sign"></span> <span class="user-warning-count" id="user-warning-count-<?php echo $id;?>"></span></p>
										<p class="col-md-1 col-md-offset-5 session-option"><span class="glyphicon glyphicon-ok-sign validate-session" id="validate-session-<?php echo $id;?>" data-session="<?php echo $id;?>" title="Valider tous les passages"></span></p>
									</div>
								</div>
							</a>
							<div class="panel-body collapse" id="body-session-<?php echo $id;?>" data-session="<?php echo $id;?>"></div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$(document).ready(function(){
				window.openedSessions = [<?php echo $id;?>];
				refreshTick();
			});

			$('#paiement').change(function(){
				var state = $('#paiement').prop('checked');
				if(state){
					$('#paiement-sub').val(1);
				} else {
					$('#paiement-sub').val(0);
				}
			});
		</script>
	</body>
</html>
