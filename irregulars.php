<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryIrregulars = $db->query("SELECT * FROM cours_participants
								JOIN users ON eleve_id_foreign=users.user_id
								JOIN cours ON cours_id_foreign=cours.cours_id
								WHERE produit_adherent_id IS NULL
								ORDER BY cours_start ASC");
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
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-pawn"></span> Participations non associées à un forfait</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-sm-10 main">
					<div class="col-lg-8 irregulars-container">
						<ul class="irregulars-list">
							<?php while($irregulars = $queryIrregulars->fetch(PDO::FETCH_ASSOC)){ ?>
							<li class="irregular-record" id="record-<?php echo $irregulars["id"];?>" data-argument="<?php echo $irregulars["id"];?>">
								<p><?php echo $irregulars["user_prenom"]." ".$irregulars["user_nom"];?> au cours de <?php echo $irregulars["cours_intitule"];?> du <?php echo date_create($irregulars["cours_start"])->format("d/m/Y\ \à\ H:i");?></p>
							</li>
							<?php } ?>
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
			$(document).on("click", ".irregular-record", function(){
				var record_id = document.getElementById($(this).attr("id")).dataset.argument;
				$.when(fetchEligibleProducts(record_id)).done(function(data){
					var construct = displayEligibleProducts(data);
					construct += "<button class='btn btn-success report-product' id='btn-product-report' data-session='"+record_id+"'>Associer à ce produit</button>";
					$(".irregulars-target-container").html(construct);
				})
			})
			/*function displayEligibleProducts(record_id){
				$(".irregulars-target-container").empty();
				$.post("functions/fetch_user_products.php", {record_id : record_id}).done(function(data){
					var product_list = JSON.parse(data), product_status;
					if(product_list == ""){
						$(".irregulars-target-container").html("Aucun produit pour cet adhérent");
					} else {
						var display_list = "<ul class='purchase-inside-list'>";
						for(var i = 0; i < product_list.length; i++){
							if(product_list[i].status == '1'){
								product_status = "item-active";
							} else {
								product_status = "item-pending";
							}
							display_list += "<li class='sub-modal-product "+product_status+"' data-argument='"+product_list[i].id+"'>";
							display_list += product_list[i].title;
							display_list += "</li>";
						}
						display_list += "</ul>";
						display_list += "<button class='btn btn-success report-product' id='btn-product-report' data-session='"+record_id+"'>Associer à ce produit</button>";
						$(".irregulars-target-container").append(display_list);
					}
				})
			}*/
		</script>
	</body>
</html>
