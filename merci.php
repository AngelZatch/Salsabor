<?php
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Merci ! | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<div class="jumbotron">
						<h1>Merci beaucoup de cet achat !</h1>
						<a href="dashboard.php" role="button" class="btn btn-default btn-block">Retour au panneau principal</a>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
		sessionStorage.clear();
		</script>
	</body>
</html>
