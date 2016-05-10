<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// User details
$details = $db->query("SELECT *, COUNT(task_title) AS count FROM users u
						JOIN tasks t ON u.user_id = t.task_target
						WHERE user_id='$data'
						AND task_token LIKE '%USR%'
						AND task_state = 0")->fetch(PDO::FETCH_ASSOC);
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Tâches concernant <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
		<link rel="stylesheet" href="assets/css/bootstrap-slider.min.css">
		<?php include "scripts.php";?>
		<script src="assets/js/tasks.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<legend><span class="glyphicon glyphicon-user"></span> Tâches</legend>
					<ul class="nav nav-tabs">
						<li role="presentation"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/historique">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/taches">Tâches</a></li>
						<?php if($details["est_professeur"] == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
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
		<script>
			$(document).ready(function(){
				moment.locale('fr');
				fetchTasks(<?php echo $data;?>, 0);
			}).on('click', '.panel-add-record', function(){
				var emptyTask = "<div class='panel task-line task-new'>";
				emptyTask += "<div class='panel-heading panel-heading-task panel-new-task container-fluid'>";
				emptyTask += "<div class='col-lg-1'>";
				emptyTask += "<div class='notif-pp'>";
				emptyTask += "<image src='<?php echo $details["photo"];?>' alt=''>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "<div class='col-sm-11'>";
				emptyTask += "<div class='row'>";
				emptyTask += "<p class='task-title col-sm-10'>";
				emptyTask += "<input class='form-control' type='text' placeholder='Titre de la tâche'>";
				emptyTask += "</p>"
				emptyTask += "<div class='container-fluid'>";
				emptyTask += "<textarea class='form-control'></textarea>";
				emptyTask += "<button class='btn btn-primary'>Valider</button>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				$(".tasks-container").append(emptyTask);
				// When validating a new task, we delete the new template one and reload the correct one. Easy!
			})
		</script>
	</body>
</html>
