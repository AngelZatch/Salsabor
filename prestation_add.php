<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$user_labels = $db->query("SELECT * FROM tags_user");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Ajouter une prestation | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/check_calendar.js"></script>
		<script src="assets/js/sessions.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-cd"></span> Ajouter une prestation
						<button class="btn btn-primary btn-add">Ajouter</button>
					</legend>
					<form method="post" role="form" class="form-horizontal" id="prestation-add-form">
						<div class="form-group">
							<label for="prestation_handler" class="col-lg-3 control-label">Prestation de <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Vous pouvez régler les noms qui vous seront suggérés avec le sélecteur 'Suggérer parmi...'"></span></label>
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
									<input type="text" class="form-control filtered-complete" id="complete-teacher" name="prestation_handler">
								</div>
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
								<textarea name="prestation_address" id="" cols="30" rows="10" class="form-control"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="prestation_description" class="col-lg-3 control-label">Détails supplémentaires</label>
							<div class="col-lg-9">
								<textarea name="prestation_description" id="" cols="30" rows="10" class="form-control"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="prestation_price" class="col-lg-3 control-label">Prix</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" name="prestation_price">
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		</div>
	<style>
		.main{
			overflow: visible;
		}
	</style>
	<script>
		$(document).ready(function(){
			$("#datepicker-start").datetimepicker({
				format: "DD/MM/YYYY HH:mm:00",
				defaultDate: moment(),
				locale: "fr",
				sideBySide: true,
				stepping: 15
			})
			$("#datepicker-end").datetimepicker({
				format: "DD/MM/YYYY HH:mm:00",
				defaultDate: moment(),
				locale: "fr",
				sideBySide: true,
				stepping: 15
			});

		}).on('click', '.btn-add', function(){
			var table = "prestations", values = $("#prestation-add-form").serialize();
			console.log(values);
			$.when(addEntry(table, values)).done(function(data){
				window.location.href = "prestation/"+data;
			})
		})
	</script>
	</body>
</html>
