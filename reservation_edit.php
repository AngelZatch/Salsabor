<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once 'functions/reservations.php';
/** Récupération des valeurs dans la base de données des champs **/
$id = $_GET['id'];
$data = $db->prepare('SELECT * FROM reservations WHERE reservation_id=?');
$data->bindParam(1, $id);
$data->execute();
$row_data = $data->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['edit'])){
	$db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$start = $_POST['date_debut']." ".$_POST['heure_debut'];
	$end = $_POST['date_fin']." ".$_POST['heure_fin'];
	try{
		$db->beginTransaction();
		$edit = $db->prepare('UPDATE cours SET cours_intitule = :intitule,
										cours_start = :cours_start,
										cours_end = :cours_end
										justification_modification = :edit_comment,
										derniere_modification = :derniere_modification
										WHERE cours_id = :id');
		$edit->bindParam(':intitule', $_POST['intitule']);
		$edit->bindParam(':cours_start', $start);
		$edit->bindParam(':cours_end', $end);
		$edit->bindParam(':edit_comment', $_POST['edit_comment']);
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
               						   <input type="submit" name="edit" role="button" class="btn btn-success" value="ENREGISTRER">
               						   <a href="planning.php" role="button" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Supprimer</a>
               					   </div> <!-- btn-toolbar -->   		
               					   <br>
               					   <p id="last-edit"><?php if($row_data['derniere_modification'] != '0000-00-00 00:00:00') echo "Dernière modification le ".date_create($row_data['derniere_modification'])->format('d/m/Y')." à ".date_create($row_data['derniere_modification'])->format('H:i');?></p>
               			<div class="form-group">
               				<input type="text" class="form-control" name="intitule" style="font-size:30px; height:inherit;" value="<?php echo $row_data['reservation_personne'];?>">
               			</div>
               			<div class="form-group">
               				<input type="date" class="col-sm-3" name="date_debut" id="date_debut" value=<?php echo date_create($row_data['reservation_start'])->format('Y-m-d');?>>
               				<input type="time" class="col-sm-3" name="heure_debut" id="heure_debut" value=<?php echo date_create($row_data['reservation_start'])->format('H:i')?>>
               							<input type="time" class="col-sm-3" name="heure_fin" id="heure_fin" value=<?php echo date_create($row_data['reservation_end'])->format('H:i');?>>
               				               	              			<input type="date" class="col-sm-3" name="date_fin" id="date_fin" value=<?php echo date_create($row_data['reservation_end'])->format('Y-m-d');?>>
               			</div>
               			<div class="form-group"></div>
               			<div class="form-group"></div>
               			<div class="form-group"></div>
               			<div class="form-group"></div>
               			<div class="form-group">
               				<label for="edit_comment">Raison de modification :</label>
               				<textarea name="edit_comment" id="edit_comment" cols="30" rows="5" class="form-control"></textarea>
               			</div>
               	</form>
               </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>