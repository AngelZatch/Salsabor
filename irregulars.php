<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryIrregulars = $db->query("SELECT * FROM cours_participants
								JOIN users ON eleve_id_foreign=users.user_id
								JOIN cours ON cours_id_foreign=cours.cours_id
								WHERE produit_adherent_id IS NULL");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-ice-lolly-tasted"></span> Participants irréguliers</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-sm-10 main">
					<?php while($irregulars = $queryIrregulars->fetch(PDO::FETCH_ASSOC)){ ?>
					<p><?php echo $irregulars["user_prenom"]." ".$irregulars["user_nom"];?> au cours de <?php echo $irregulars["cours_intitule"];?> du <?php echo $irregulars["cours_start"];?></p>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
