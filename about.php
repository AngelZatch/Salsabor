<?php
session_start();
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>A propos | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6"><p class="page-title"><span class="glyphicon glyphicon-question-sign"></span> A propos de Salsabor Gestion</p></div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-lg-10 col-lg-offset-2 main">
					<p>Version de l'application : 1.3.2 mise à jour le 11/09/2015</p>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
