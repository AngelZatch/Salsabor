<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$display = $_GET["display"];
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Participations irrégulières | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script src="assets/js/participations.js"></script>
		<script src="assets/js/jquery.waypoints.min.js"></script>
		<script>
			$(document).ready(function(){
				<?php if($display == "all"){ ?>
				displayIrregularParticipations(0);
				<?php } else { ?>
				displayIrregularUsers();
				<?php } ?>
			}).on('show.bs.collapse', '.panel-collapse', function(){
				var user_id = document.getElementById($(this).attr("id")).dataset.user;
				displayIrregularUserParticipations(user_id);
			}).on('click', '.glyphicon-button-alt', function(e){
				e.stopPropagation();
				var user_id = document.getElementById($(this).attr("id")).dataset.user;
				window.top.location = "user/"+user_id+"/historique";
			})
		</script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-bishop"></span> Participations irrégulières</legend>
					<p class="sub-legend irregular-participations-title"><span></span> participations irrégulières.</p>
					<ul class="nav nav-tabs">
						<li role="presentation" <?php if($display == "all") echo "class='active'";?>>
							<a href="regularisation/participations/all">Tout afficher</a>
						</li>
						<li role="presentation" <?php if($display == "user") echo "class='active'";?>><a href="regularisation/participations/user">Par utilisateur</a></li>
					</ul>
					<?php if($display == "all"){ ?>
					<div class="container-fluid irregular-sessions-container">
						<ul class="irregulars-list">

						</ul>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
	</body>
</html>
