<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
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
				displayIrregularParticipations(0, 0);
				<?php }
				if($display == "user"){ ?>
				displayIrregularUsers();
				<?php }
				if($display == "old"){ ?>
				displayIrregularParticipations(0, 1);
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
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-bishop"></span> Participations irrégulières</legend>
					<ul class="nav nav-tabs">
						<li role="presentation" <?php if($display == "all") echo "class='active'";?>>
							<a href="regularisation/participations/all">Tout afficher</a>
						</li>
						<li role="presentation" <?php if($display == "user") echo "class='active'";?>><a href="regularisation/participations/user">Par utilisateur</a></li>
						<li role="presentation" <?php if($display == "old") echo "class='active'";?>><a href="regularisation/participations/old">Archivées</a></li>
					</ul>

					<?php if($display == "all" || $display == "old"){
	if($display == "all"){ ?>
					<p class="sub-legend irregular-participations-title"><span></span> participations irrégulières.</p>
					<?php } ?>
					<div class="container-fluid irregular-sessions-container">
						<ul class="irregulars-list">

						</ul>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<?php include "inserts/delete_modal.php";?>
	</body>
</html>
