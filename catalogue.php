<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

// On obtient la liste des produits
$listeProduits = $db->query("SELECT * FROM produits");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Catalogue de produits | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-10 main">
					<h1 class="page-title"> Acheter des produits</h1>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="25" aria-valuemax="100" style="width:25%;">
							<span class="glyphicon glyphicon-th"></span> Etape 1/4 : Choix des produits
						</div>
					</div>
					<div class="btn-toolbar">
						<a href="dashboard.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Annuler et retourner à l'accueil</a>
						<a href="personnalisation.php" role="button" class="btn btn-success" name="next"><span class="glyphicon glyphicon-erase"></span> Valider les achats <span class="glyphicon glyphicon-arrow-right"></span></a>
					</div> <!-- btn-toolbar -->
					<div class="row">
						<?php while($produits = $listeProduits->fetch(PDO::FETCH_ASSOC)){?>
						<div class="col-sm-6 col-md-4">
							<div class="thumbnail">
								<div class="caption">
									<p class="thumbnail-title"><?php echo $produits["produit_nom"];?></p>
									<p><?php echo $produits["description"];?></p>
									<input type="hidden" value="<?php echo $produits["produit_id"];?>">
									<a href="#" class="btn btn-primary btn-block" role="button" name="add-shopping">Ajouter au panier</a>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
					<a href="" role="button" class="btn btn-success btn-block" name="next"><span class="glyphicon glyphicon-erase"></span> Valider les achats <span class="glyphicon glyphicon-arrow-right"></span></a>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				composeURL();
				$("[name='add-shopping']").click(function(){
					var produit_id = $(this).parents("div").children("input").val();
					var produit_nom = $(this).parents("div").children(".thumbnail-title").html();
					sessionStorage.setItem('produit_id-'+lowestSpot, produit_id);
					sessionStorage.setItem('produit-demo-'+lowestSpot, produit_nom);
					notifPanier();
					composeURL();
				});
			});
		</script>
	</body>
</html>
