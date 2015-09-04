<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$queryAdherentsNav = $db->query("SELECT * FROM users ORDER BY user_nom ASC");
$array_eleves_nav = array();
while($adherents_nav = $queryAdherentsNav->fetch(PDO::FETCH_ASSOC)){
	array_push($array_eleves_nav, $adherents_nav["user_prenom"]." ".$adherents_nav["user_nom"]);
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
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-sm-10 main">
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
		</script>
	</body>
</html>
<script>
</script>
