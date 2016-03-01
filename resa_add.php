<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once 'functions/reservations.php';

$queryPrestations = $db->query('SELECT * FROM prestations WHERE est_resa=1');
$queryLieux = $db->query('SELECT * FROM salle WHERE est_salle_cours=1');

$queryAdherentsNom = $db->query("SELECT * FROM users ORDER BY user_nom ASC");
$array_eleves = array();
while($adherents = $queryAdherentsNom->fetch(PDO::FETCH_ASSOC)){
	array_push($array_eleves, $adherents["user_prenom"]." ".$adherents["user_nom"]);
}

// Ajout d'une réservation
if(isset($_POST['addResa'])){
	addResa();
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Réserver une salle | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<form method="post" target="_blank" role="form" id="add_resa">
			<div class="fixed">
				<div class="col-lg-6">
					<p class="page-title"><span class="glyphicon glyphicon-record"></span> Effectuer une réservation</p>
				</div>
				<div class="col-lg-6">
					<div class="btn-toolbar">
						<a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
						<input type="submit" name="addResa" role="button" class="btn btn-primary confirm-add" value="ENREGISTRER" id="submit-button" disabled>
					</div> <!-- btn-toolbar -->
				</div>
			</div>
			<div class="container-fluid">
				<div class="row">
					<?php include "side-menu.php";?>
					<div class="col-sm-10 main">
						<div class="form-group">
							<label for="identite" class="control-label">Demandeur</label>
							<input type="text" name="identite_nom" id="identite_nom" class="form-control mandatory has-check has-name-completion input-lg" placeholder="Nom">
						</div>
						<div class="form-group">
							<label for="prestation" class="control-label">Activité</label>
							<select name="prestation" id="prestation" class="form-control mandatory input-lg" onChange="checkCalendar(true, false)">
								<?php while($prestations = $queryPrestations->fetch(PDO::FETCH_ASSOC)){?>
								<option value="<?php echo $prestations['prestations_id'];?>"><?php echo $prestations['prestations_name'];?></option>";
								<?php } ?>
							</select>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="date_debut" class="control-label">Date <span class="span-mandatory">*</span></label>
									<div class="input-group input-group-lg">
										<input type="date" class="form-control mandatory" name="date_debut" id="date_debut" onChange="checkHoliday()">
										<span role="button" class="input-group-btn">
											<a class="btn btn-info" role="button" date-today="true">Insérer aujourd'hui</a>
										</span>
									</div>
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label for="heure_debut" class="control-label">Début à</label>
									<input type="time" class="form-control mandatory input-lg" id="heure_debut" name="heure_debut" onChange="checkCalendar(true, false)">
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label for="heure_fin" class="control-label">Fin à</label>
									<input type="time" class="form-control mandatory input-lg" id="heure_fin" name="heure_fin" onChange="checkCalendar(true, false)">
								</div>
							</div>
							<p class="error-alert" id="holiday-alert"></p>
						</div>
						<div class="form-group">
							<label for="lieu" class="control-label">Salle</label>
							<select name="lieu" class="form-control mandatory input-lg" id="lieu" onChange="checkCalendar(true, false)">
								<?php while($lieux = $queryLieux->fetch(PDO::FETCH_ASSOC)){?>
								<option value="<?php echo $lieux['salle_id'];?>"><?php echo $lieux['salle_name'];?></option>;
								<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<label for="priorite" class="control-label">Réservation payée <span class="label-tip">Une réservation payée ne peut plus être supprimée au profit d'un cours.</span></label>
							<input name="priorite" id="priorite" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
						</div>
						<div class="form-group" id="prix_reservation">
							<label for="prix_resa" class="control-label">Prix de la réservation : </label>
							<div class="input-group input-group-lg">
								<input type="number" step="any" name="prix_resa" id="prix_calcul" class="form-control" aria-describedby="currency-addon">
								<span class="input-group-addon" id="currency-addon">€</span>
							</div>
							<input type="checkbox" unchecked data-toggle="toggle" data-on="Payée" data-off="Due" data-onstyle="success" data-offstyle="danger" id="paiement">
							<input type="hidden" name="paiement" id="paiement-sub" value="0">
						</div>
						<div class="align-right">
							<p class="error-alert" id="error_message"></p>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php include "scripts.php";?>
		<script src="assets/js/check_calendar.js"></script>
		<script>
			if($('#priorite').attr('value') == 0){
				$('#prix_reservation').hide();
			}
			$('#priorite').change(function(){
				$('#prix_reservation').toggle('600');
			});
			$('#paiement').change(function(){
				var state = $('#paiement').prop('checked');
				if(state){
					$('#paiement-sub').val(1);
				} else {
					$('#paiement-sub').val(0);
				}
			});

			$(document).ready(function(){
				var listeAdherents = JSON.parse('<?php echo json_encode($array_eleves);?>');
				$(".has-name-completion").autocomplete({
					source: listeAdherents
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
				$("#submit-button").click(function(){
					setTimeout(function() {
						window.location.href = "planning.php";
					}, 5000);
				})
			});
		</script>
	</body>
</html>
