<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

if(isset($_POST["add"])){
	$volume_horaire = 0;
	if($_POST["volume_horaire"] != 0){
		$tarif_horaire = $_POST["tarif_global"]/$_POST["volume_horaire"];
		$volume_horaire = $_POST["volume_horaire"];
	} else {
		$tarif_horaire = 0;
	}
	$validite = 7 * $_POST["validite"];
	$actif = 1;
	if(isset($_POST["arep"])){
		$arep = $_POST["arep"];
	} else {
		$arep = 0;
	}

	try{
		$db->beginTransaction();
		$new = $db->prepare("INSERT INTO produits(produit_nom, description, volume_horaire, validite_initiale, tarif_horaire, tarif_global, date_activation ,date_desactivation, actif, echeances_paiement, autorisation_report, est_recharge, est_illimite, est_sans_engagement, est_cours_particulier, est_formation_professionnelle, est_abonnement, est_autre)
		VALUES(:intitule, :description, :volume_horaire, :validite, :tarif_horaire, :tarif_global, :date_activation, :date_limite_achat, :actif, :echeances, :autorisation_report, :est_recharge, :est_illimite, :est_sans_engagement, :est_cours_particulier, :est_formation_professionnelle, :est_abonnement, :est_autre)");
		$new->bindParam(':intitule', $_POST["intitule"]);
		$new->bindParam(':description', $_POST["description"]);
		$new->bindParam(':volume_horaire', $_POST["volume_horaire"]);
		$new->bindParam(':validite', $validite);
		$new->bindParam(':tarif_horaire', $tarif_horaire);
		$new->bindParam(':tarif_global', $_POST["tarif_global"]);
		$new->bindParam(':date_activation', $_POST["date_activation"]);
		$new->bindParam(':date_limite_achat', $_POST["date_limite_achat"]);
		$new->bindParam(':actif', $actif);
		$new->bindParam(':echeances', $_POST["echeances"]);
		$new->bindParam(':autorisation_report', $arep);
		$new->bindParam(':est_recharge', $_POST["est_recharge"]);
		$new->bindParam(':est_illimite', $_POST["est_illimite"]);
		$new->bindParam(':est_sans_engagement', $_POST["est_sans_engagement"]);
		$new->bindParam(':est_cours_particulier', $_POST["est_cours_particulier"]);
		$new->bindParam(':est_formation_professionnelle', $_POST["est_formation_professionnelle"]);
		$new->bindParam(':est_abonnement', $_POST["est_abonnement"]);
		$new->bindParam(':est_autre', $_POST["est_autre"]);
		$new->execute();
		$db->commit();
		header("Location: forfaits.php");
	}catch (PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Ajouter un forfait | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<form action="forfait_add.php" class="form-horizontal" method="post">
						<legend><span class="glyphicon glyphicon-plus"></span> Ajouter un forfait
							<input type="submit" name="add" role="button" class="btn btn-primary" value="ENREGISTRER">
						</legend>
						<div class="form-group">
							<label for="intitule" class="control-label col-lg-3">Intitulé</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="intitule" placeholder="Nom du produit">
							</div>
						</div>
						<div class="form-group">
							<label for="flags" class="control-label col-lg-3">Type de produit</label><br>
							<div class="col-lg-9">
								<label for="est_recharge" class="control-label">Recharge Liberté</label>
								<input name="est_recharge" id="est_recharge" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
								<label for="est_illimite" class="control-label">Illimité</label>
								<input name="est_illimite" id="est_illimite" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
								<label for="est_sans_engagement" class="control-label">Sans Engagement</label>
								<input name="est_sans_engagement" id="est_sans_engagement" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
								<label for="est_cours_particulier" class="control-label">Cours Particulier</label>
								<input name="est_cours_particulier" id="est_cours_particulier" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
								<label for="est_formation_professionnelle" class="contorl-label">Formation Pro.</label>
								<input name="est_formation_professionnelle" id="est_formation_professionnelle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
								<label for="est_abonnement" class="contorl-label">Abonnement</label>
								<input name="est_abonnement" id="est_abonnement" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
								<label for="est_autre" class="contorl-label">Divers</label>
								<input name="est_autre" id="est_autre" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
							</div>
						</div>
						<div class="form-group">
							<label for="description" class="col-lg-3 control-label">Description</label>
							<div class="col-lg-9">
								<textarea rows="5" class="form-control" name="description" placeholder=""></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="volume_horaire" class="col-lg-3 control-label">Volume de cours (en heures)</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" name="volume_horaire" placeholder="Exemple : 10">
							</div>
						</div>
						<div class="form-group">
							<label for="validite" class="col-lg-3 control-label">Durée de validité</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" name="validite" placeholder="Exemple : 48">
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
									<input type="number" step="any" class="form-control" name="tarif_global">
									<span class="input-group-addon">€</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="echeances" class="col-lg-3 control-label">Nombre d'échéances autorisées</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" name="echeances">
							</div>
						</div>
						<p class="sub-legend">Période de vente</p>
						<span class="label-tip">Dans le cas d'une offre promotionnelle limitée dans le temps</span>
						<div class="form-group">
							<label for="date_activation" class="col-lg-3 control-label">Ouverture à l'achat</label>
							<div class="col-lg-9">
								<div class="input-group">
									<input type="date" class="form-control" name="date_activation">
									<span role="button" class="input-group-btn">
										<a class="btn btn-info" role="button" date-today="true">Insérer aujourd'hui</a>
									</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="date_limite_achat" class="col-lg-3 control-label">Fermeture à l'achat</label>
							<div class="col-lg-9">
								<div class="input-group">
									<input type="date" class="form-control" name="date_limite_achat">
									<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" date-today="true">Insérer aujourd'hui</a></span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$("#est_illimite").change(function(){
				if($(this).val() == '1'){
					$("#volume_horaire").hide('600');
				} else {
					$("#volume_horaire").show('600');
				}
			});
		</script>
	</body>
</html>
