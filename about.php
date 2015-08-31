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
				<div class="col-sm-10 main">
					<p id="current-time"></p>
					<h1 class="page-title"><span class="glyphicon glyphicon-question-sign"></span> A propos de Salsabor Gestion</h1>
					<p>Version de l'application : 1.1.2 mise à jour le 31/08/2015</p>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
