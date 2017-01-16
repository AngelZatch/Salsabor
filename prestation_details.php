<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$prestation_id = $_GET["id"];
$prestation = $db->query("SELECT *, CONCAT(u.user_prenom, ' ', u.user_nom) AS handler FROM prestations p
JOIN users u ON p.prestation_handler = u.user_id
WHERE prestation_id = $prestation_id")->fetch();

$user_labels = $db->query("SELECT * FROM tags_user");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Prestation de <?php echo $prestation['handler'];?> du <?php echo date_create($prestation['prestation_start'])->format('d/m/Y');?> de <?php echo date_create($prestation['prestation_start'])->format('H:i')?> à <?php echo date_create($prestation['prestation_end'])->format('H:i');?> | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/check_calendar.js"></script>
		<script src="assets/js/tasks-js.php"></script>
		<script src="assets/js/tags.js"></script>
		<script src="assets/js/sessions.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend>
						<span class="glyphicon glyphicon-cd"></span> Prestation de <?php echo $prestation['handler'];?> du <?php echo date_create($prestation['prestation_start'])->format('d/m/Y');?> de <?php echo date_create($prestation['prestation_start'])->format('H:i')?> à <?php echo date_create($prestation['prestation_end'])->format('H:i');?>
						<div class="btn-toolbar float-right">
							<button class="btn btn-success btn-edit" id="submit-button"><span class="glyphicon glyphicon-ok"></span> Enregistrer les modifications</button>
							<button class="btn btn-danger btn-delete"><span class="glyphicon glyphicon-trash"></span> Supprimer</button>
							<input type="hidden" name="id" value="<?php echo $prestation_id;?>">
						</div> <!-- btn-toolbar -->
					</legend>
					<p class="sub-legend">Détails</p>
					<form name="prestation_details" id="prestation_details" role="form" class="form-horizontal">
						<div class="form-group">
							<label for="prestation_handler" class="col-lg-3 control-label">Prestation de</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="prestation_handler" value="<?php echo $prestation['handler']?>">
							</div>
						</div>
						<div class="form-group">
							<label for="prestation_start" class="col-lg-3 control-label">Début</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="prestation_start" id="datepicker-start">
							</div>
						</div>
						<div class="form-group">
							<label for="prestation_end" class="col-lg-3 control-label">Fin</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="prestation_end" id="datepicker-end">
							</div>
						</div>
						<div class="form-group">
							<label for="prestation_address" class="col-lg-3 control-label">Adresse</label>
							<div class="col-lg-9">
								<textarea name="prestation_address" id="" cols="30" rows="10" class="form-control"><?php echo $prestation["prestation_address"];?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="prestation_description" class="col-lg-3 control-label">Détails supplémentaires</label>
							<div class="col-lg-9">
								<textarea name="prestation_description" id="" cols="30" rows="10" class="form-control"><?php echo $prestation["prestation_description"];?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="prestation_price" class="col-lg-3 control-label">Prix</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" name="prestation_price" value="<?php echo $prestation["prestation_price"];?>">
							</div>
						</div>
					</form>
					<p class="sub-legend top-divider">Tâches à faire</p>
					<div class="tasks-container container-fluid"></div>
					<div class="sub-container container-fluid">
						<div class="panel-heading panel-add-record container-fluid">
							<div class="col-sm-1"><div class="notif-pp empty-pp"></div></div>
							<div class="col-sm-11 new-task-text">Ajouter une nouvelle tâche...</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include "inserts/edit_modal.php";?>
		<script>
			$(document).ready(function(){
				$("#datepicker-start").datetimepicker({
					format: "DD/MM/YYYY HH:mm:00",
					defaultDate: "<?php echo date_create($prestation['prestation_start'])->format("m/d/Y H:i");?>",
					locale: "fr",
					sideBySide: true,
					stepping: 15
				})
				$("#datepicker-end").datetimepicker({
					format: "DD/MM/YYYY HH:mm:00",
					defaultDate: "<?php echo date_create($prestation['prestation_end'])->format("m/d/Y H:i");?>",
					locale: "fr",
					sideBySide: true,
					stepping: 15
				});

				fetchTasks("PRS", <?php echo $prestation_id;?>, 0, null, 0);
			}).on('click', '.panel-add-record', function(){
				var emptyTask = "<div class='panel task-line task-new panel-new-task'>";
				emptyTask += "<div class='panel-heading container-fluid'>";
				emptyTask += "<div class='col-lg-1'>";
				emptyTask += "<div class='notif-pp'>";
				emptyTask += "<image src='' alt=''>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "<div class='col-sm-11'>";
				emptyTask += "<div class='row'>";
				emptyTask += "<p class='task-title col-sm-10'>";
				emptyTask += "<input class='form-control task-title-input' type='text' placeholder='Titre de la tâche'>";
				emptyTask += "</p>"
				emptyTask += "<div class='container-fluid'>";
				emptyTask += "<input class='form-control' id='task-target-input' type='hidden' value='[PRS-<?php echo $prestation_id;?>]'>";
				emptyTask += "<textarea class='form-control task-description-input'></textarea>";
				emptyTask += "<button class='btn btn-primary post-task' id='post-task-button'>Valider</button>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				$(".tasks-container").append(emptyTask);
				// When validating a new task, we delete the new template one and reload the correct one. Easy!
			}).on('click', '.btn-edit', function(){
				var values = $("#prestation_details").serialize(), table = "prestations", prestation_id = <?php echo $prestation_id;?>;
				updateEntry(table, values, prestation_id).done(function(data){
					console.log(data);
					showNotification("Modifications enregistrées", "success");
				});
			}).on('click', '.btn-delete', function(){
				var booking_id = <?php echo $prestation_id;?>;
				$.when(deleteEntry("reservations", booking_id), deleteTasksByTarget("PRS", booking_id)).done(function(){
					window.top.location = "planning";
				})
			})
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
