<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once "functions/cours.php";

$cours_name = $db->query('SELECT DISTINCT cours_intitule FROM cours');
$arr_cours_name = array();
while($row_cours_name = $cours_name->fetch(PDO::FETCH_ASSOC)){
	array_push($arr_cours_name, trim(preg_replace('/[0-9]+/', '', $row_cours_name['cours_intitule'])));
}

$lieux = $db->query('SELECT * FROM rooms');

$user_labels = $db->query("SELECT * FROM tags_user");

// Ajout d'un cours
if(isset($_POST['add'])){
	addCours();
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Ajouter un cours | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<form method="post" role="form" class="form-horizontal">
					<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
						<legend><span class="glyphicon glyphicon-plus"></span> Ajouter un cours
							<input type="submit" name="add" role="button" class="btn btn-primary" value="ENREGISTRER" id="submit-button" disabled>
						</legend>
						<div class="form-group">
							<label for="intitule" class="col-lg-3 control-label">Intitulé du cours</label>
							<div class="col-lg-9">
								<input type="text" class="form-control mandatory" name="intitule" id="session_name_input" placeholder="Nom du cours">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Professeur <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Vous pouvez régler les noms qui vous seront suggérés avec le sélecteur 'Suggérer parmi...'"></span></label>
							<div class="col-lg-9">
								<div class="input-group">
									<div class="input-group-btn">
										<button type="button" class="btn btn-default dropdown-toggle suggestion-text" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Suggérer parmi... <span class="caret"></span></button>
										<ul class="dropdown-menu dropdown-custom">
											<?php while($user_label = $user_labels->fetch(PDO::FETCH_ASSOC)){ ?>
											<li class="completion-option"><a><?php echo $user_label["rank_name"];?></a></li>
											<?php } ?>
											<li class="completion-option"><a>Ne pas suggérer</a></li>
										</ul>
									</div>
									<input type="text" class="form-control filtered-complete" id="complete-teacher" name="prof_principal">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Début</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="cours_start" id="datepicker-start">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Fin</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="cours_end" id="datepicker-end">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Récurrence</label>
							<div class="col-lg-9">
								<input name="recurrence" id="recurrence" data-toggle="checkbox-x" data-three-state="false" data-size="lg" value="0">
							</div>
						</div>
						<div class="form-group" id="recurring-options" style="display:none;">
							<span class="help-block col-lg-9 col-lg-offset-3">Par défaut, la récurrence est hebdomadaire</span>
							<div class="form-group">
								<label for="" class="col-lg-3 control-label">Nombre de récurrences</label>
								<div class="col-lg-9">
									<input type="number" class="form-control" id="steps" name="steps">
								</div>
							</div>
							<div class="form-group">
								<label for="date_fin" class="col-lg-3 control-label">Fin de récurrence</label>
								<div class="col-lg-9">
									<input type="text" class="form-control" name="date_fin" id="date_fin">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="lieu" class="col-lg-3 control-label">Lieu</label>
							<div class="col-lg-9">
								<select name="lieu" class="form-control mandatory" id="lieu" onChange="checkCalendar(false, false)">
									<?php while($row_lieux = $lieux->fetch(PDO::FETCH_ASSOC)){ ?>
									<option value="<?php echo $row_lieux['room_id'];?>"><?php echo $row_lieux['room_name'];?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="align-right">
							<p id="error_message"></p>
						</div>
					</div>
				</form>
			</div>
		</div>
		<style>
			.main{
				overflow: visible;
			}
		</style>
		<?php include "scripts.php";?>
		<script src="assets/js/check_calendar.js"></script>
		<script src="assets/js/sessions.js"></script>
		<script>
			$(document).ready(function(){
				var start = sessionStorage.getItem('start');
				var default_start, default_end;
				if(start != null){
					var format_start = new Date(start).toISOString();
					var end = sessionStorage.getItem('end');
					var format_end = new Date(end).toISOString();
					var default_start = moment(format_start).format('YYYY-MM-DD HH:mm:ss');
					var default_end = moment(format_end).format('YYYY-MM-DD HH:mm:ss');
				} else {
					var format_start = new Date().toISOString();
					var default_start = moment(format_start).startOf('hour').add(1, 'h').format('YYYY-MM-DD HH:mm:ss');
					var default_end = moment(format_start).startOf('hour').add(2, 'h').format('YYYY-MM-DD HH:mm:ss');
				}
				var start_day = moment(format_start).format('YYYY-MM-DD');
				$("#datepicker-start").datetimepicker({
					format: "YYYY-MM-DD HH:mm:00",
					defaultDate: default_start,
					locale: "fr",
					sideBySide: true,
					stepping: 30
				});
				$("#datepicker-end").datetimepicker({
					format: "YYYY-MM-DD HH:mm:00",
					defaultDate: default_end,
					locale: "fr",
					sideBySide: true,
					stepping: 30
				});
				$("#date_fin").datetimepicker({
					format : "YYYY-MM-DD",
					locale: 'fr',
					debug: true
				}).on('dp.change', function(e){
					console.log("changed");
					if(!$("#steps").is(":focus")){
						var end_date = $(this).val();
						var starting_date = moment($("#datepicker-start").val()).format("YYYY-MM-DD");
						if(moment(end_date).isValid()){
							var delta = moment(moment(end_date).diff(starting_date));
							var delta_days = delta / (7 * 24 * 3600 * 1000);
							$("#steps").val(Math.trunc(delta_days));
						}
					}
				})
				var coursNameTags = JSON.parse('<?php echo json_encode($arr_cours_name);?>');
				$('#cours_tags').autocomplete({
					source: coursNameTags
				});

				sessionStorage.removeItem('end');
				sessionStorage.removeItem('start');
			})
			$("#recurrence").change(function(){
				$("#recurring-options").toggle('600');
			});
			$("#steps").keyup(function(){
				var steps = $(this).val();
				var starting_date = moment($("#datepicker-start").val()).format("YYYY-MM-DD");
				var end_date = moment(starting_date).add(steps, 'w').format("YYYY-MM-DD")
				$("#date_fin").val(end_date);
			})
		</script>
	</body>
</html>
