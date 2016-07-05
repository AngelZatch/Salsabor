<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

// On obtient la liste des produits
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
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-shopping-cart"></span> Vente de produits <a href="personnalisation.php" role="button" class="btn btn-success" name="next"><span class="glyphicon glyphicon-erase"></span> Valider <span class="glyphicon glyphicon-arrow-right"></span></a>
					</legend>
					<div class="content container-fluid">
						<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="33" aria-valuemin="33" aria-valuemax="100" style="width:33.33%;">
								<span class="glyphicon glyphicon-th"></span> Etape 1/3 : Choix des produits
							</div>
						</div>
						<?php
						// Product list
						$listeProduits = $db->query("SELECT * FROM produits ORDER BY est_autre, est_formation_professionnelle, est_cours_particulier, est_sans_engagement, est_abonnement, est_illimite, est_recharge DESC");
						$previous = -1;
						while($produits = $listeProduits->fetch(PDO::FETCH_ASSOC)){
							$current = $produits["est_recharge"].$produits["est_illimite"].$produits["est_abonnement"].$produits["est_sans_engagement"].$produits["est_cours_particulier"].$produits["est_formation_professionnelle"].$produits["est_autre"];
							if($previous != $current){
								switch($current){
									case '1000000':
									case '1000001':
										echo "<legend>Recharges</legend>";
										break;

									case '0100000':
										echo "<legend>Illimités</legend>";
										break;

									case '0010000':
										echo "<legend>Abonnements</legend>";
										break;

									case '0001000':
										echo "<legend>Sans engagement</legend>";
										break;

									case '0000100':
										echo "<legend>Cours particuliers</legend>";
										break;

									case '0000010':
										echo "<legend>Formation professionnelle</legend>";
										break;

									case '0000001':
										echo "<legend>Autres produits</legend>";
										break;

									default:
										echo "<legend>Autres</legend>";
								}
							}
						?>
						<div class="col-sm-6 col-md-4 col-lg-4 panel-product-container">
							<div class="panel panel-product">
								<div class="panel-body">
									<p class="product-title"><?php echo $produits["produit_nom"];?></p>
									<?php $labels = $db->prepare("SELECT * FROM assoc_product_tags apt
						JOIN tags_session ts ON apt.tag_id_foreign = ts.rank_id
						WHERE product_id_foreign = ?
						ORDER BY tag_color DESC");
							$labels->bindParam(1, $produits["produit_id"], PDO::PARAM_INT);
							$labels->execute(); ?>
									<p>Valable <?php echo $produits["validite_initiale"]/7;?> semaines</p>
									<div class="tags-display">
										<h5>
											<?php while($label = $labels->fetch(PDO::FETCH_ASSOC)){
								if($label["is_mandatory"] == 1){
									$label_name = "<span class='glyphicon glyphicon-star'></span> ".$label["rank_name"];
								} else {
									$label_name = $label["rank_name"];
								}
											?>
											<span class="label label-salsabor" title="Supprimer l'étiquette" id="product-tag-<?php echo $label["entry_id"];?>" data-target="<?php echo $label["entry_id"];?>" data-targettype="product" style="background-color:<?php echo $label["tag_color"];?>"><?php echo $label_name;?></span>
											<?php } ?>
										</h5>
									</div>
									<?php if($produits["description"] != ""){ ?>
									<p class="product-description"><?php echo $produits["description"];?></p>
									<?php } else { ?>
									<p class="product-description purchase-sub">Pas de description</p>
									<?php } ?>
									<p class="product-price"><?php echo $produits["tarif_global"];?> €</p>
									<input type="hidden" value="<?php echo $produits["produit_id"];?>">
									<a href="#" class="btn btn-primary btn-block" role="button" name="add-shopping">Ajouter au panier</a>
								</div>
							</div>
						</div>
						<?php
							$previous = $current;
						} ?>
					</div>
					<a href="" role="button" class="btn btn-success btn-block" name="next"><span class="glyphicon glyphicon-erase"></span> Valider les achats <span class="glyphicon glyphicon-arrow-right"></span></a>
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
					var produit_nom = $(this).parents("div").children(".product-title").html();
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
