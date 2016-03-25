<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryIrregulars = $db->query("SELECT * FROM cours_participants
								JOIN users ON eleve_id_foreign=users.user_id
								JOIN cours ON cours_id_foreign=cours.cours_id
								WHERE produit_adherent_id IS NULL
								ORDER BY user_nom, cours_start ASC");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-pawn"></span> Participations non associées à un forfait</legend>
					<div class="col-lg-8 irregulars-container">
						<ul class="irregulars-list">
							<?php
							$currentUser = "";
							while($irregulars = $queryIrregulars->fetch(PDO::FETCH_ASSOC)){
							if($currentUser != $irregulars["user_nom"]){
								echo "<a href='user/".$irregulars["user_id"]."' class='sub-legend'>".$irregulars["user_prenom"]." ".$irregulars["user_nom"]."</a>";
							}
							?>
							<li class="irregular-participation" id="participation-<?php echo $irregulars["id"];?>" data-argument="<?php echo $irregulars["id"];?>">
								<p><?php echo $irregulars["user_prenom"]." ".$irregulars["user_nom"];?> au cours de <?php echo $irregulars["cours_intitule"];?> du <?php echo date_create($irregulars["cours_start"])->format("d/m/Y\ \à\ H:i");?></p>
							</li>
							<?php $currentUser = $irregulars["user_nom"];
							} ?>
						</ul>
					</div>
					<div class="col-lg-3 irregulars-target-container">
						Forfaits de l'adhérent associé au passage sélectionné
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script>
			$(document).on("click", ".irregular-participation", function(){
				var participation_id = document.getElementById($(this).attr("id")).dataset.argument;
				$(".irregular-participation").removeClass("focused");
				$(this).addClass("focused");
				$.when(fetchEligibleProducts(participation_id)).done(function(data){
					var construct = displayEligibleProducts(data);
					construct += "<button class='btn btn-default btn-modal report-product' id='btn-product-report' data-session='"+participation_id+"'><span class='glyphicon glyphicon-arrow-right'></span> Associer</button> ";
					construct += "<button class='btn btn-danger pre-delete' data-session='"+participation_id+"' id='btn-record-delete'><span class='glyphicon glyphicon-trash'></span> Supprimer</button>";
					$(".irregulars-target-container").html(construct);
				})
			}).on("click", ".pre-delete", function(){
				$(this).addClass("delete-participation");
				$(this).removeClass("pre-delete");
				$(this).html("<span class='glyphicon glyphicon-trash'></span> Confirmer</button>");
			})
		</script>
	</body>
</html>
