<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-pencil"></span> Page Template</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-lg-10 col-lg-offset-2 main">
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
