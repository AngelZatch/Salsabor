<?php
require_once 'functions/db_connect.php';

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
	$db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$start = $_POST['date_debut']." ".$_POST['heure_debut'];
	$end = $_POST['date_fin']." ".$_POST['heure_fin'];
	try{
		$db->beginTransaction();
		$edit = $db->prepare('UPDATE cours SET cours_intitule = :intitule,
										cours_start = :cours_start,
										cours_end = :cours_end
										WHERE cours_id = :id');
		$edit->bindParam(':intitule', $_POST['intitule']);
		$edit->bindParam(':cours_start', $start);
		$edit->bindParam(':cours_end', $end);
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
	
	$db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	try{
		$db->beginTransaction();
		for($i = 1; $i < $nombre_repetitions; $i++){
			$edit = $db->prepare('UPDATE cours SET cours_intitule = :intitule,
											cours_start = :cours_start,
											cours_end = :cours_end
							WHERE cours_parent_id = :parent_id AND cours_id = :id');
			$edit->bindParam(':intitule', $_POST['intitule']);
			$edit->bindParam(':cours_start', $start);
			$edit->bindParam(':cours_end', $end);
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
					   <a href="planning.php" role="button" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Supprimer</a>
					   <div class="collapse" id="save-options">
					   	<div class="well">
					   		<input type="submit" name="editOne" role="button" class="btn btn-success" value="Cet évènement">
					   		<input type="submit" name="editNext" role="button" class="btn btn-success" value="Tous les suivants">
					   		<button class="btn btn-primary">Toute la série</button>
					   	</div>
					   </div>
				   </div> <!-- btn-toolbar -->   		
				   <br>
               		<div class="form-group">
               			<div class="col-sm-9">
               				<input type="text" class="form-control" name="intitule" style="font-size:30px; height:inherit;" value=<?php echo $row_data['cours_intitule'];?>>
               			</div>
               		</div>
               		<div class="form-group">
               			<div class="col-sm-9">
               				<input type="date" class="col-sm-3" name="date_debut" id="date_debut" value=<?php echo date_create($row_data['cours_start'])->format('Y-m-d');?>>
               				<input type="time" class="col-sm-3" name="heure_debut" id="heure_debut" value=<?php echo date_create($row_data['cours_start'])->format('H:i')?>>
							<input type="time" class="col-sm-3" name="heure_fin" id="heure_fin" value=<?php echo date_create($row_data['cours_end'])->format('H:i');?>>
              			<input type="date" class="col-sm-3" name="date_fin" id="date_fin" value=<?php echo date_create($row_data['cours_end'])->format('Y-m-d');?>>
               			</div>
               		</div>
               		<div class="form-group">
               			<div class="col-sm-9">
               				<select name="type" id="" class="form-control">
               					<?php
								$types = $db->query('SELECT * FROM prestations WHERE est_cours=1');
								while($row_types = $types->fetch(PDO::FETCH_ASSOC)){
									echo"<option value=".$row_types['prestations_id'].">".$row_types['prestations_name']."</option>";
								}
								?>
               				</select>
               			</div>
               		</div>
               		<div class="form-group"></div>
               		<div class="form-group"></div>
               		<div class="form-group"></div>
               		<div class="form-group"></div>
               </form>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>