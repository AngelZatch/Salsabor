<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Notifications | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-list-alt"></span> Tâches à faire</legend>
					<div class="tasks-container container-fluid"></div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/tasks.js"></script>
		<script>
			$(document).ready(function(){
				moment.locale('fr');
				fetchTasks(0, 0);
			})
		</script>
	</body>
</html>
<script>
</script>
