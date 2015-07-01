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
		$edit = $db->prepare('UPDATE reservations SET reservation_personne = :demandeur,
										type_prestation = :type,
										reservation_start = :start,
										reservation_end = :end,
										reservation_salle = :lieu,
										priorite =:priorite,
										paiement_effectue = :paiement,
										justification_modification = :edit_comment,
										derniere_modification = :derniere_modification
										WHERE reservation_id = :id');
		$edit->bindParam(':demandeur', $_POST['demandeur']);
		$edit->bindParam(':type', $_POST['type']);
		$edit->bindParam(':start', $start);
		$edit->bindParam(':end', $end);
		$edit->bindParam(':lieu', $_POST['lieu']);
		$edit->bindParam(':priorite', $_POST['priorite']);
		$edit->bindParam(':paiement', $_POST['paiement']);
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

if(isset($_POST['delete'])){
    deleteResa();
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
					   <input type="submit" name="edit" role="button" class="btn btn-primary" value="ENREGISTRER">
					   <input type="submit" name="delete" role="button" class="btn btn-danger" value="SUPPRIMER">
					   <input type="hidden" name="id" value="<?php echo $id;?>">
				   </div> <!-- btn-toolbar -->   		
				   <br>
				   <p id="last-edit"><?php if($row_data['derniere_modification'] != '0000-00-00 00:00:00') echo "Dernière modification le ".date_create($row_data['derniere_modification'])->format('d/m/Y')." à ".date_create($row_data['derniere_modification'])->format('H:i');?></p>
					<div class="form-group">
						<input type="text" class="form-control" name="demandeur" style="font-size:30px; height:inherit;" value="<?php echo $row_data['reservation_personne'];?>">
					</div>
					<div class="form-group">
						<input type="date" class="col-sm-3" name="date_debut" id="date_debut" value=<?php echo date_create($row_data['reservation_start'])->format('Y-m-d');?>>
						<input type="time" class="col-sm-3" name="heure_debut" id="heure_debut" value=<?php echo date_create($row_data['reservation_start'])->format('H:i')?>>
						<input type="time" class="col-sm-3" name="heure_fin" id="heure_fin" value=<?php echo date_create($row_data['reservation_end'])->format('H:i');?>>
						<input type="date" class="col-sm-3" name="date_fin" id="date_fin" value=<?php echo date_create($row_data['reservation_end'])->format('Y-m-d');?>>
					</div>
					<div class="form-group">
						<select name="type" id="" class="form-control">
						<?php
						$types = $db->query('SELECT * FROM prestations WHERE est_resa=1');
						while($row_types = $types->fetch(PDO::FETCH_ASSOC)){
							echo"<option value=".$row_types['prestations_id'].">".$row_types['prestations_name']."</option>";
						}
						?>
						</select>
					</div>
					<div class="form-group">
						<select name="lieu" id="" class="form-control">
							<?php
							$lieux = $db->query('SELECT * FROM salle');
									while($row_lieux = $lieux->fetch(PDO::FETCH_ASSOC)){
										echo "<option value=".$row_lieux['salle_id'].">".$row_lieux['salle_name']."</option>";
									}
							?>
						</select>
					</div>
					<div class="form-group">
						<label for="priorite" class="cbx-label">Réservation payée</label>
						<input name="priorite" id="priorite" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $row_data['priorite']?>">
						<label for="priorite">Une réservation payée ne peut plus être supprimée au profit d'un cours.</label>
					</div>
					<div class="form-group" id="prix_reservation">
						<p>Prix de la réservation : <span id='prix_calcul'><?php echo $row_data['reservation_prix'];?> €</span>
							<input type="checkbox" <?php if($row_data['paiement_effectue'] == '0') echo "unchecked"; else echo "checked";?> data-toggle="toggle" data-on="Payée" data-off="Due" data-onstyle="success" data-offstyle="danger" style="float:left;" id="paiement">
							<input type="hidden" name="paiement" id="paiement-sub" value="<?php echo $row_data['paiement_effectue'];?>">
						</p>
					</div>
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
   <script>
   if($('#priorite').attr('value') == 0){
	   $('#prix_reservation').hide();
   }
	   $('#priorite').change(function(){
		   $('#prix_reservation').toggle('600');
	   })
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