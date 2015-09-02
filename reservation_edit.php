<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once 'functions/reservations.php';
/** Récupération des valeurs dans la base de données des champs **/
$id = $_GET['id'];
$queryReservation = $db->prepare('SELECT * FROM reservations JOIN users ON reservation_personne=users.user_id WHERE reservation_id=?');
$queryReservation->bindParam(1, $id);
$queryReservation->execute();
$reservation = $queryReservation->fetch(PDO::FETCH_ASSOC);

$queryTypes = $db->query('SELECT * FROM prestations WHERE est_resa=1');
$queryLieux = $db->query('SELECT * FROM salle');

if(isset($_POST['edit'])){
	$db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$start = $_POST['date_debut']." ".$_POST['heure_debut'];
	$end = $_POST['date_debut']." ".$_POST['heure_fin'];
	try{
		$db->beginTransaction();
		$edit = $db->prepare('UPDATE reservations SET reservation_personne = :demandeur,
										type_prestation = :prestation,
										reservation_start = :start,
										reservation_end = :end,
										reservation_salle = :lieu,
										priorite =:priorite,
										paiement_effectue = :paiement,
										justification_modification = :edit_comment,
										derniere_modification = :derniere_modification
										WHERE reservation_id = :id');
		$edit->bindParam(':demandeur', $reservation["reservation_personne"]);
		$edit->bindParam(':prestation', $_POST['prestation']);
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
		<title>Edit - Résevation par <?php echo $reservation['user_prenom']." ".$reservation['user_nom'];?> le <?php echo date_create($reservation['reservation_start'])->format('d/m/Y');?> de <?php echo date_create($reservation['reservation_start'])->format('H:i')?> à <?php echo date_create($reservation['reservation_end'])->format('H:i');?> | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<form method="post" role="form">
					<div class="fixed">
						<div class="col-lg-6">
							<p class="page-title">Réservation</p>
						</div>
						<div class="col-lg-6">
							<div class="btn-toolbar">
								<a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
								<input type="submit" name="edit" role="button" class="btn btn-primary" value="ENREGISTRER">
								<input type="submit" name="delete" role="button" class="btn btn-danger" value="SUPPRIMER">
								<input type="hidden" name="id" value="<?php echo $id;?>">
							</div> <!-- btn-toolbar -->
						</div>
					</div>
					<div class="col-sm-10 main">
						<p id="last-edit"><?php if($reservation['derniere_modification'] != '0000-00-00 00:00:00') echo "Dernière modification le ".date_create($reservation['derniere_modification'])->format('d/m/Y')." à ".date_create($reservation['derniere_modification'])->format('H:i');?></p>
						<div class="form-group">
							<input type="text" class="form-control" name="identite_prenom" style="font-size:30px; height:inherit;" value="<?php echo $reservation['user_prenom']." ".$reservation['user_nom'];?>">
						</div>
						<div class="form-group">
							<input type="date" class="col-sm-4" name="date_debut" id="date_debut" onChange="checkCalendar(true, false)" value=<?php echo date_create($reservation['reservation_start'])->format('Y-m-d');?>>
							<input type="time" class="col-sm-4" name="heure_debut" id="heure_debut" onChange="checkCalendar(true, false)" value=<?php echo date_create($reservation['reservation_start'])->format('H:i')?>>
							<input type="time" class="col-sm-4" name="heure_fin" id="heure_fin" onChange="checkCalendar(true, false)" value=<?php echo date_create($reservation['reservation_end'])->format('H:i');?>>
						</div>
						<div class="form-group">
							<select name="prestation" id="prestation" class="form-control" onChange="checkCalendar(true, false)">
								<?php while($type = $queryTypes->fetch(PDO::FETCH_ASSOC)){
	if($reservation["type_prestation"] == $type["prestations_id"]){?>
								<option selected="selected" value="<?php echo $type['prestations_id'];?>"><?php echo $type['prestations_name'];?></option>
								<?php } else { ?>
								<option value="<?php echo $type['prestations_id'];?>"><?php echo $type['prestations_name'];?></option>
								<?php }
} ?>
							</select>
						</div>
						<div class="form-group">
							<select name="lieu" id="lieu" class="form-control" onChange="checkCalendar(true, false)">
								<?php while($lieux = $queryLieux->fetch(PDO::FETCH_ASSOC)){
	if($reservation["reservation_salle"] == $lieux["salle_id"]){?>
								<option selected="selected" value="<?php echo $lieux['salle_id'];?>"><?php echo $lieux['salle_name'];?></option>
								<?php } else { ?>
								<option value="<?php echo $lieux['salle_id'];?>"><?php echo $lieux['salle_name'];?></option>
								<?php }
} ?>
							</select>
						</div>
						<div class="form-group">
							<label for="priorite" class="cbx-label">Réservation payée</label>
							<input name="priorite" id="priorite" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $reservation['priorite']?>">
							<label for="priorite">Une réservation payée ne peut plus être supprimée au profit d'un cours.</label>
						</div>
						<div class="form-group" id="prix_reservation">
							<label for="prix_resa" class="control-label">Prix de la réservation : </label>
							<div class="input-group">
								<span class="input-group-addon" id="currency-addon">€</span>
								<input type="number" name="prix_resa" id="prix_calcul" class="form-control" value="<?php echo $reservation['reservation_prix'];?>" aria-describedby="currency-addon">
							</div>
							<input type="checkbox" <?php if($reservation['paiement_effectue'] == '0') echo "unchecked"; else echo "checked";?> data-toggle="toggle" data-on="Payée" data-off="Due" data-onstyle="success" data-offstyle="danger" style="float:left;" id="paiement">
							<input type="hidden" name="paiement" id="paiement-sub" value="<?php echo $reservation['paiement_effectue'];?>">
						</div>
						<div class="form-group">
							<label for="edit_comment">Raison de modification :</label>
							<textarea name="edit_comment" id="edit_comment" cols="30" rows="5" class="form-control"></textarea>
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
