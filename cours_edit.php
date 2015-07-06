<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once 'functions/cours.php';
/** Récupération des valeurs dans la base de données des champs **/
$id = $_GET['id'];
$data = $db->prepare('SELECT * FROM cours WHERE cours_id=?');
$data->bindParam(1, $id);
$data->execute();
$row_data = $data->fetch(PDO::FETCH_ASSOC);

$data = $db->prepare('SELECT recurrence, frequence_repetition, parent_end_date FROM cours_parent WHERE parent_id=?');
$data->bindParam(1, $row_data['cours_parent_id']);
$data->execute();
$res_recurrence = $data->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['editOne'])){
	$db = PDOFactory::getConnection();
	$start = $_POST['date_debut']." ".$_POST['heure_debut'];
	$end = $_POST['date_fin']." ".$_POST['heure_fin'];
	$paiement = $_POST['paiement'];
    $prix_final = $_POST['prix_cours'];
	try{
		$db->beginTransaction();
		$edit = $db->prepare('UPDATE cours SET cours_intitule = :intitule,
										cours_start = :cours_start,
										cours_end = :cours_end,
                                        cours_prix = :prix,
										paiement_effectue = :paiement,
                                        justification_modification = :edit_comment,
										derniere_modification = :derniere_modification
										WHERE cours_id = :id');
		$edit->bindParam(':intitule', $_POST['intitule']);
		$edit->bindParam(':cours_start', $start);
		$edit->bindParam(':cours_end', $end);
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
	header('Location: planning.php');
}

if(isset($_POST['editNext'])){
	/** Edition de tous les suivants. Il faut donc rétablir la récurrence en cas de changement de dates **/
	$start = $_POST['date_debut']." ".$_POST['heure_debut'];
	$end = $_POST['date_fin']." ".$_POST['heure_fin'];
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
			$edit->bindParam(':parent_id', $row_data['cours_parent_id']);
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
	header('Location: planning.php');
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
    <title>Template - Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
           <br>
              <div class="col-sm-9">
               <form method="post" class="form-horizontal" role="form">
				   <div class="btn-toolbar">
					   <a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
					   <?php
						if($res_recurrence == '0'){
							echo "<input type='submit' name='editOne' role='button' class='btn btn-success' value='ENREGISTRER'>";
						} else {
							echo "<a href='#save-options' class='btn btn-primary' role='button' data-toggle='collapse' aria-expanded='false' aria-controls='saveOptions'>ENREGISTRER</a>";
						}
					   ?>
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
				   <br>
              		<p id="last-edit"><?php if($row_data['derniere_modification'] != '0000-00-00 00:00:00') echo "Dernière modification le ".date_create($row_data['derniere_modification'])->format('d/m/Y')." à ".date_create($row_data['derniere_modification'])->format('H:i');?></p>
               		<div class="form-group">
               			<input type="text" class="form-control" name="intitule" style="font-size:30px; height:inherit;" value=<?php echo $row_data['cours_intitule'];?>>
               		</div>
               		<div class="form-group">
               			<input type="date" class="col-sm-3" name="date_debut" id="date_debut" value=<?php echo date_create($row_data['cours_start'])->format('Y-m-d');?>>
               			<input type="time" class="col-sm-3" name="heure_debut" id="heure_debut" value=<?php echo date_create($row_data['cours_start'])->format('H:i')?>>
               			<input type="time" class="col-sm-3" name="heure_fin" id="heure_fin" value=<?php echo date_create($row_data['cours_end'])->format('H:i');?>>
               			<input type="date" class="col-sm-3" name="date_fin" id="date_fin" value=<?php echo date_create($row_data['cours_end'])->format('Y-m-d');?>>
               		</div>
               		<div class="form-group">
               			<select name="type" id="" class="form-control">
               				<?php
							$types = $db->query('SELECT * FROM prestations WHERE est_cours=1');
							while($row_types = $types->fetch(PDO::FETCH_ASSOC)){
								echo"<option value=".$row_types['prestations_id'].">".$row_types['prestations_name']."</option>";
							}
							?>
               			</select>
               		</div>
               		<div class="form-group">
               			<div class="panel panel-default">
               			<div class="panel-heading">               			
							<label for="professeur">Professeur : </label>
							<?php
							$data = $db->prepare('SELECT * FROM professeurs WHERE prof_id=?');
							$data->bindParam(1, $row_data['prof_principal']);
							$data->execute();
							$data_prof = $data->fetch(PDO::FETCH_ASSOC);
							?>
							<p>
								<?php echo $data_prof['prenom']." ".$data_prof['nom'];?>
							</p>
						</div>
							<div class="panel-body">
									<label for="liste_participants">Participants enregistrés :</label>
									<input type="text" for="liste_participants" class="form-control" placeholder="Ajouter un participant">
							</div>
							<ul class="list-group">
								<?php
								$liste_participants = $db->prepare('SELECT * FROM cours_participants JOIN adherents ON eleve_id_foreign=adherents.eleve_id WHERE cours_id_foreign=?');
								$liste_participants->bindParam(1, $id);
								$liste_participants->execute();
								while($row_liste_participants = $liste_participants->fetch(PDO::FETCH_ASSOC)){
									echo "<li class='list-group-item'>".$row_liste_participants['eleve_prenom']." ".$row_liste_participants['eleve_nom']."</li>";
								}
								$nombre_eleves = $liste_participants->rowCount();
								?>
								<li class="list-group-item" id="prix-calcul">Somme due à l'enseignant :
								<?php
								$data = $db->prepare('SELECT * FROM tarifs_professeurs WHERE prof_id_foreign=? AND type_prestation=?');
								$data->bindParam(1, $row_data['prof_principal']);
								$data->bindParam(2, $row_data['cours_type']);
								$data->execute();
								$data_tarif = $data->fetch(PDO::FETCH_ASSOC);

								// Calcul de la somme due à l'enseignant en fonction de la table des tarifs
								if(isset($data_tarif['ratio_multiplicatif'])){
									if($data_tarif['ratio_multiplicatif'] == 'personne'){
										$prix_final = $data_tarif['tarif_prestation'] * $nombre_eleves;
									} else if ($data_tarif['ratio_multiplicatif'] == 'prestation'){
										$prix_final = $data_tarif['tarif_prestation'];
									} else{
										$prix_final = $data_tarif['tarif_prestation'] * $row_data['cours_unite'];
									}
								} else{
									$prix_final = $data_tarif['cout_horaire'] * $data_tarif['cours_unite'];
								}
								echo "<span class='input-group-addon' id='currency-addon'>€</span><input type=text name='prix_cours' id='prix_calcul' class='form-control' value=".$prix_final." aria-describedby='currency-addon'>";
								?>
								<input type="checkbox" <?php if($row_data['paiement_effectue'] == '0') echo "unchecked"; else echo "checked";?> data-toggle="toggle" data-on="Payée" data-off="Due" data-onstyle="success" data-offstyle="danger" style="float:left;" id="paiement">
								<input type="hidden" name="paiement" id="paiement-sub" value="<?php echo $row_data['paiement_effectue'];?>">
								</li>
							</ul>
               			</div>
               		</div>
               		<div class="form-group">
                          <label for="edit-comment">Raison de modification :</label>
                          <textarea name="edit-comment" id="edit-comment" cols="30" rows="5" class="form-control"></textarea>
               		</div>
               		<div class="form-group"></div>
               </form>
               </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script>
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