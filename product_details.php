<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET["id"];

// Product details
$queryProduit = $db->prepare("SELECT * FROM produits WHERE produit_id=?");
$queryProduit->bindParam(1, $data, PDO::PARAM_INT);
$queryProduit->execute();
$produit = $queryProduit->fetch(PDO::FETCH_ASSOC);

// Labels
$labels = $db->prepare("SELECT * FROM assoc_product_tags apt
						JOIN tags_session ts ON apt.tag_id_foreign = ts.rank_id
						WHERE product_id_foreign = ?
						ORDER BY tag_color DESC");
$labels->bindParam(1, $data, PDO::PARAM_INT);
$labels->execute();

if(isset($_POST["edit"])){
	if(isset($_POST["volume_horaire"])){
		$tarif_horaire = $_POST["tarif_global"]/$_POST["volume_horaire"];
	} else {
		$tarif_horaire = 0;
	}
	if($_POST["validite_jour"] == "1"){
		$validite = $_POST["validite"];
	} else {
		$validite = 7 * $_POST["validite"];
	}
	$actif = 1;

	try{
		$db->beginTransaction();
		$edit = $db->prepare("UPDATE produits SET produit_nom = :intitule,
												description = :description,
												volume_horaire = :volume_horaire,
												validite_initiale = :validite,
												tarif_horaire = :tarif_horaire,
												tarif_global = :tarif_global,
												actif = :actif,
												echeances_paiement = :echeances,
												autorisation_report = :autorisation_report,
												WHERE produit_id = :id");
		$edit->bindParam(':intitule', $_POST["intitule"]);
		$edit->bindParam(':description', $_POST["description"]);
		$edit->bindParam(':volume_horaire', $_POST["volume_horaire"]);
		$edit->bindParam(':validite', $validite);
		$edit->bindParam(':tarif_horaire', $tarif_horaire);
		$edit->bindParam(':tarif_global', $_POST["tarif_global"]);
		$edit->bindParam(':actif', $actif);
		$edit->bindParam(':echeances', $_POST["echeances"]);
		$edit->bindParam(':autorisation_report', $_POST["arep"]);
		$edit->bindParam(':id', $data);
		$edit->execute();
		$db->commit();
	}catch (PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Détails du forfait <?php echo $produit["produit_nom"];?> | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/tags.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<form action="" class="form-horizontal" method="post">
						<legend><span class="glyphicon glyphicon-credit-card"></span> <?php echo $produit["produit_nom"];?>
							<input type="submit" name="edit" role="button" class="btn btn-primary hidden-xs" value="Enregistrer">
						</legend>
						<div class="form-group">
							<label for="intitule" class="control-label col-lg-3">Intitulé</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="intitule" value="<?php echo $produit["produit_nom"];?>" placeholder="Nom du produit">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">&Eacute;tiquettes</label>
							<div class="col-lg-9 session-tags">
								<h4>
									<?php while($label = $labels->fetch(PDO::FETCH_ASSOC)){
	if($label["is_mandatory"] == 1){
		$label_name = "<span class='glyphicon glyphicon-star'></span> ".$label["rank_name"];
	} else {
		$label_name = $label["rank_name"];
	}
									?>
									<span class="label label-salsabor label-clickable label-deletable" title="Supprimer l'étiquette" id="product-tag-<?php echo $label["entry_id"];?>" data-target="<?php echo $label["entry_id"];?>" data-targettype="product" style="background-color:<?php echo $label["tag_color"];?>"><?php echo $label_name;?></span>
									<?php } ?>
									<span class="label label-default label-clickable label-add trigger-sub" id="label_add" data-subtype="session-tags" data-targettype="product" title="Ajouter une étiquette">+</span>
								</h4>
							</div>
						</div>
						<div class="form-group">
							<label for="description" class="col-lg-3 control-label">Description</label>
							<div class="col-lg-9">
								<textarea rows="5" class="form-control" name="description" value="<?php echo $produit["description"];?>" placeholder=""></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="volume_horaire" class="col-lg-3 control-label">Volume de cours (en heures)</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" name="volume_horaire" value="<?php echo $produit["volume_horaire"];?>" placeholder="Exemple : 10">
							</div>
						</div>
						<div class="form-group">
							<label for="validite" class="col-lg-3 control-label">Durée de validité</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" name="validite" value="<?php echo $produit["validite_initiale"];?>" placeholder="Exemple : 48">
								<label for="est_recharge" class="control-label">Jours</label>
								<input name="validite_jour" id="validite_jour" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="1"><span class="label-tip">Si décoché, la durée sera calculée en semaines.</span>
							</div>
						</div>
						<div class="form-group">
							<label for="arep" class="col-lg-3 control-label">Autoriser l'extension de validité ?</label>
							<div class="col-lg-9">
								<input name="arep" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="1">
							</div>
						</div>
						<div class="form-group">
							<label for="tarif_global" class="col-lg-3 control-label">Prix d'achat</label>
							<div class="col-lg-9">
								<div class="input-group">
									<input type="number" step="any" class="form-control" name="tarif_global" id="product-price" value="<?php echo $produit["tarif_global"];?>">
									<span class="input-group-addon">€</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="echeances" class="col-lg-3 control-label">Nombre d'échéances autorisées</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" name="echeances" value="<?php echo $produit["echeances_paiement"];?>">
							</div>
						</div>
						<input type="submit" name="edit" role="button" class="btn btn-primary btn-block visible-xs" value="Enregistrer">
					</form>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
	</body>
</html>
