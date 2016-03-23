<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once 'functions/cours.php';
/** Récupération des valeurs dans la base de données des champs **/
$id = $_GET['id'];
if(isset($_GET["drive"])){
	$drive = $_GET["drive"];
}
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

$queryParticipants = $db->prepare('SELECT * FROM cours_participants JOIN users ON eleve_id_foreign=users.user_id WHERE cours_id_foreign=?');
$queryParticipants->bindParam(1, $id);
$queryParticipants->execute();
$nombre_eleves = $queryParticipants->rowCount();

$queryTarif = $db->prepare('SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=? AND type_prestation=?');
$queryTarif->bindParam(1, $cours['prof_principal']);
$queryTarif->bindParam(2, $cours['cours_type']);
$queryTarif->execute();
$tarif = $queryTarif->fetch(PDO::FETCH_ASSOC);

$queryTypes = $db->query('SELECT * FROM prestations WHERE est_cours=1');

$querySalles = $db->query("SELECT * FROM salle WHERE est_salle_cours=1");

$queryEleves = $db->query("SELECT * FROM users ORDER BY user_nom ASC");
$array_eleves = array();
while($eleves = $queryEleves->fetch(PDO::FETCH_ASSOC)){
	array_push($array_eleves, $eleves["user_prenom"]." ".$eleves["user_nom"]);
}

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
	if(isset($_GET["drive"])){
		header('Location: passages.php');
	} else {
		header('Location: planning.php');
	}
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
	if(isset($_GET["drive"])){
		header('Location: passages.php');
	} else {
		header('Location: planning.php');
	}
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
		<title>Editer - <?php echo $cours['cours_intitule'];?> (<?php echo date_create($cours['cours_start'])->format('d/m/Y');?> : <?php echo date_create($cours['cours_start'])->format('H:i')?> / <?php echo date_create($cours['cours_end'])->format('H:i');?>) | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<form method="post" role="form">
					<div class="fixed">
						<div class="col-lg-6">
							<p class="page-title"><span class="glyphicon glyphicon-eye-open"></span> Cours de <?php echo $cours['cours_intitule'];?></p>
						</div>
						<div class="col-lg-6">
							<div class="btn-toolbar">
								<?php if(isset($_GET["drive"])){ ?>
								<a href="passages.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour aux passages</a>
								<?php } else { ?>
								<a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
								<?php } if($res_recurrence == '0'){ ?>
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
							<input type="text" class="form-control" name="intitule" style="font-size:30px; height:inherit;" value="<?php echo $cours['cours_intitule'];?>">
						</div>
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label for="" class="control-label">Date</label>
									<input type="date" class="col-sm-3 form-control input-lg" name="date_debut" id="date_debut" value="<?php echo date_create($cours['cours_start'])->format('Y-m-d');?>">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label for="" class="control-label">Heure de début</label>
									<input type="time" class="col-sm-3 form-control input-lg" name="heure_debut" id="heure_debut" value="<?php echo date_create($cours['cours_start'])->format('H:i')?>">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label for="" class="control-label">Heure de fin</label>
									<input type="time" class="col-sm-3 form-control input-lg" name="heure_fin" id="heure_fin" value="<?php echo date_create($cours['cours_end'])->format('H:i');?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="type" class="control-label">Type de cours</label>
									<select name="type" class="form-control input-lg">
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
							<div class="col-lg-6">
								<div class="form-group">
									<label for="salle" class="control-label">Salle</label>
									<select name="salle" class="form-control input-lg">
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
						</div>
						<div class="form-group">
							<div class="panel panel-default">
								<div class="panel-heading">
									<label for="professeur">Professeur : </label>
									<p>
										<?php echo $data_prof['user_prenom']." ".$data_prof['user_nom'];?>
									</p>
								</div>
								<div class="panel-body">
									<label for="liste_participants">Participants enregistrés :</label>
									<div class="input-group input-group-lg">
										<input type="text" for="liste_participants" class="form-control" id="liste-participants" placeholder="Ajouter un participant">
										<span role="buttton" class="input-group-btn" id="add-eleve"><a class="btn btn-info" role="button">Ajouter l'élève</a></span>
									</div>
								</div>
								<ul class="list-group">
									<?php while($participants = $queryParticipants->fetch(PDO::FETCH_ASSOC)){?>
									<li class='list-group-item'>
										<span class="glyphicon glyphicon-<?php echo (isset($participants["produit_adherent_id"]))?"ok":"remove";?>"></span>
										<?php echo $participants['user_prenom']." ".$participants['user_nom'];?>
										<span class="list-item-option delete-participant glyphicon glyphicon-trash" title="Supprimer l'élève de ce cours"><input type="hidden" value="<?php echo $participants["user_id"];?>"></span>
									</li>
									<?php } ?>
									<li class="list-group-item" id="prix-calcul">Somme due à l'enseignant :
										<div class="input-group">
											<span class='input-group-addon' id='currency-addon'>€</span>
											<input type="number" step="any" name='prix_cours' id='prix_calcul' class='form-control' value="<?php echo $cours["cours_prix"];?>" aria-describedby='currency-addon'>
										</div>
										<input type="checkbox" <?php if($cours['paiement_effectue'] == '0') echo "unchecked"; else echo "checked";?> data-toggle="toggle" data-on="Payée" data-off="Due" data-onstyle="success" data-offstyle="danger" style="float:left;" id="paiement">
										<input type="hidden" name="paiement" id="paiement-sub" value="<?php echo $cours['paiement_effectue'];?>">
									</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="edit-comment">Raison de modification :</label>
							<textarea name="edit-comment" id="edit-comment" cols="30" rows="5" class="form-control"></textarea>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var listeAdherents = JSON.parse('<?php echo json_encode($array_eleves);?>');
				$("#liste-participants").autocomplete({
					source: listeAdherents,
					select: $(this).empty()
				});

				$("#add-eleve").click(function(){
					var adherent = $("#liste-participants").val();
					var cours_id = $("[name='id']").val();
					$.post("functions/add_participant.php", {cours_id : cours_id, adherent : adherent}).done(function(data){
						showSuccessNotif(data);
						var line = "<li class='list-group-item'>";
						line += adherent;
						line += "<span class='list-item-option delete-participant glyphicon glyphicon-trash' title='Supprimer l\'élève de ce cours'>";
						line += "<input type='hidden' value="+data+">";
						line += "</span>";
						line += "</li>";
						$(line).insertBefore($("#prix-calcul"));
						$("#liste-participants").empty();
					});
				})
			});

			$('#paiement').change(function(){
				var state = $('#paiement').prop('checked');
				if(state){
					$('#paiement-sub').val(1);
				} else {
					$('#paiement-sub').val(0);
				}
			});

			$(".delete-participant").click(function(){
				var clicked = $(this);
				var cours_id = $("[name='id']").val();
				var delete_id = clicked.children("input").val();
				$.post("functions/delete_participant.php", {cours_id, delete_id}).done(function(data){
					$.notify("Participation supprimée.", {globalPosition:"right bottom", className:"success"});
					clicked.parent(".list-group-item").hide('200');
				})
			});
		</script>
	</body>
</html>
