<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>A propos | Salsabor</title>
		<?php include "includes.php";?>
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
				<div class="col-sm-10 main">
					<p>Version de l'application : 1.3.0 mise à jour le 08/09/2015</p>
					<p>Bonjour ? Que faites-vous là, à errer sur cette page (presque) vide ? Si vous attendez l'historique des mises à jour de l'application, restez accroché, il arrive bientôt !</p>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
