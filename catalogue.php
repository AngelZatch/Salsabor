<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

// On obtient la liste des produits
$listeProduits = $db->query("SELECT * FROM produits ORDER BY produit_nom ASC");
if(isset($_GET["user"])){
	$beneficiaireInitial = $_GET["user"];
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Catalogue de produits | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-shopping-cart"></span> Acheter des produits</p>
					</div>
					<div class="col-lg-6">
						<div class="btn-toolbar">
							<a href="dashboard.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Annuler et retourner à l'accueil</a>
							<a href="personnalisation.php" role="button" class="btn btn-success" name="next"><span class="glyphicon glyphicon-erase"></span> Valider les achats <span class="glyphicon glyphicon-arrow-right"></span></a>
						</div> <!-- btn-toolbar -->
					</div>
				</div>
				<div class="col-sm-10 main">
					<div class="content">
						<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="33" aria-valuemin="33" aria-valuemax="100" style="width:33.33%;">
								<span class="glyphicon glyphicon-th"></span> Etape 1/3 : Choix des produits
							</div>
						</div>
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
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				// Bénéficiaire principal si la procédure a été entammée sur la page d'un utilisateur
				<?php if(isset($_GET["user"])){ ?>
				sessionStorage.setItem("beneficiaireInitial", '<?php echo $beneficiaireInitial;?>');
				<?php } else { ?>
				sessionStorage.removeItem("beneficiaireInitial");
				<?php } ?>
				$("[name='add-shopping']").click(function(){
					if(sessionStorage.getItem("panier") == null){
						var globalCart = [];
						var globalCartNames = [];
					} else {
						var globalCart = JSON.parse(sessionStorage.getItem("panier"));
						var globalCartNames = JSON.parse(sessionStorage.getItem("panier-noms"));
						composeURL(globalCart[0]);
					}
					var produit_id = $(this).parents("div").children("input").val();
					var produit_nom = $(this).parents("div").children(".thumbnail-title").html();
					globalCart.push(produit_id);
					globalCartNames.push(produit_nom);
					sessionStorage.setItem("panier", JSON.stringify(globalCart));
					sessionStorage.setItem("panier-noms", JSON.stringify(globalCartNames));
					composeURL(globalCart[0]);
					notifPanier();
				});
			});
		</script>
	</body>
</html>
