<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryProduits = $db->query("SELECT * FROM produits");

$queryAdherentsNom = $db->query("SELECT * FROM users ORDER BY user_nom ASC");
$array_eleves = array();
while($adherents = $queryAdherentsNom->fetch(PDO::FETCH_ASSOC)){
	array_push($array_eleves, $adherents["user_prenom"]." ".$adherents["user_nom"]);
}

$listePanier = $db->query("SELECT * FROM panier JOIN produits ON panier_element=produits.produit_id");
$row_panier = $listePanier->fetchAll();
$quantitePanier = $listePanier->rowCount();

$prixTotal = 0;
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Vente | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-8 main" id="right-bordered">
					<h1 class="page-title">Acheter des produits</h1>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="25" aria-valuemax="100" style="width:50%;">
							<span class="glyphicon glyphicon-erase"></span> Etape 2/4 : Personnalisation des produits
						</div>
					</div>
					<div class="btn-toolbar">
						<a href="catalogue.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> <span class="glyphicon glyphicon-th"></span> Retourner au catalogue</a>
						<a href="paiement.php" role="button" id="check-memory" class="btn btn-default" input="submit"><span class="glyphicon glyphicon-repeat"></span> Règlement des achats <span class="glyphicon glyphicon-arrow-right"></span></a>
					</div><!-- btn-toolbar -->
					<?php foreach($row_panier as $p){ ?>
					<section id="details-<?php echo $p["panier_order"];?>">
						<p id="produit-title-<?php echo $p["panier_order"];?>" class="produit-title"><?php echo $p["produit_nom"];?></p>
						<span role="button" class="input-group-btn">
							<a href="#produit-details-<?php echo $p["panier_order"];?>" class="btn btn-default btn-block" data-toggle="collapse" aria-expanded="false"><span class="glyphicon glyphicon-search"></span> Détails...</a>
						</span>
						<div id="produit-details-<?php echo $p["panier_order"];?>" class="collapse">
							<div id="produit-content" class="well">
								<?php if($p["produit_nom"]=="Invitation"){?>
								<p>Cette invitation est à usage unique. Si elle n'est pas liée à un cours, sa période de validité est alors de <?php echo $p["validite_initiale"];?> jours.</p>
								<?php } else { ?>
								<p>Cet abonnement est valable pendant <?php echo $p["validite_initiale"]/7;?> semaines.</p>
								<?php } ?>
								<p>Il donne accès à <?php echo $p["volume_horaire"];?> heures de cours pendant toute sa durée d'activation.</p>
								<p>L'extension de durée (AREP) n'est pas autorisée.</p>
							</div>
						</div>
						<div class="form-group"> <!-- Bénéficiaire -->
							<label for="personne">Bénéficiaire</label>
							<input type="text" name="identite_nom" id="identite_nom-<?php echo $p["panier_order"];?>" class="form-control" placeholder="Nom">
							<p class="error-alert" id="err_adherent"></p>
							<div class="alert alert-danger" id="unpaid" style="display:none;"><strong>Cet adhérent a des échéances impayées. Impossible de continuer la procédure</strong></div>
							<a href="#user-details" role="button" class="btn btn-info" value="create-user" id="create-user" style="display:none;" data-toggle="collapse" aria-expanded="false" aria-controls="userDetails">Ouvrir le formulaire de création</a>
							<div id="user-details" class="collapse">
								<div class="well">
									<div class="form-group">
										<input type="text" name="identite_prenom" id="identite_prenom" class="form-control" placeholder="Prénom">
									</div>
									<div class="form-group">
										<label for="" class="control-label">Adresse postale</label>
										<input type="text" name="rue" id="rue" placeholder="Adresse" class="form-control">
									</div>
									<div class="form-group">
										<input type="text" name="code_postal" id="code_postal" placeholder="Code Postal" class="form-control">
									</div>
									<div class="form-group">
										<input type="text" name="ville" id="ville" placeholder="Ville" class="form-control">
									</div>
									<div class="form-group">
										<label for="text" class="control-label">Adresse mail</label>
										<input type="mail" name="mail" id="mail" placeholder="Adresse mail" class="form-control">
									</div>
									<div class="form-group">
										<label for="telephone" class="control-label">Numéro de téléphone</label>
										<input type="text" name="telephone" id="telephone" placeholder="Numéro de téléphone" class="form-control">
									</div>
									<div class="form-group">
										<label for="date_naissance" class="control-label">Date de naissance</label>
										<input type="date" name="date_naissance" id="date_naissance" class="form-control">
									</div>
									<a class="btn btn-primary" onClick="addAdherent()">AJOUTER</a>
								</div>
							</div>
						</div>
						<div id="maturities-checked">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="date_activation">Date d'activation <span class="label-tip">Par défaut : activation au premier passage</span></label>
										<div class="input-group">
											<input type="date" name="date_activation" id="date_activation-<?php echo $p["panier_order"];?>" class="form-control" onchange="evaluateExpirationDate()">
											<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" date-today="true" onclick="evaluateExpirationDate()">Insérer aujourd'hui</a></span>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="date_expiration">Date indicative d'expiration</label>
										<input type="date" name="date_expiration" class="form-control">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="promotion-e">Réduction (en €)</label>
										<div class="input-group">
											<span class="input-group-addon"><input type="radio" id="promotion-euros-<?php echo $p["panier_order"];?>" name="promotion" class="checkbox-x">Réduction en €</span>
											<input type="text" name="promotion-e-<?php echo $p["panier_order"];?>" class="form-control">
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="promotion-p">Réduction (en %)</label>
										<div class="input-group">
											<span class="input-group-addon"><input type="radio" name="promotion" id="promotion-pourcent-<?php echo $p["panier_order"];?>">Réduction en %</span>
											<input type="text" name="promotion-p-<?php echo $p["panier_order"];?>" class="form-control">
										</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="prix_achat">Montant</label>
								<div class="input-group">
									<span class="input-group-addon">€</span>
									<input type="hidden" id="prix-fixe-<?php echo $p["panier_order"];?>" value="<?php echo $p["tarif_global"];?>">
									<input type="text" name="prix_achat" id="prix-calcul-<?php echo $p["panier_order"];?>" class="form-control prix-display" value="<?php echo $p["tarif_global"];?>">
								</div>
							</div>
						</div>
					</section>
					<?php $prixTotal += $p["tarif_global"];?>
					<?php } ?>
				</div>
				<div class="col-sm-2 shopping-section">
					<h4><span class="glyphicon glyphicon-shopping-cart"></span> Panier en cours</h4>
					<ul class="nav nav-pills nav-stacked">
						<?php foreach($row_panier as $p){
	if($p === reset($row_panier)) {?>
						<li id="details-<?php echo $p["panier_order"];?>-toggle" class="active" style="cursor:pointer;" role="presentation"><a href="#"><?php echo $p["produit_nom"];?></a></li>
						<?php } else { ?>
						<li id="details-<?php echo $p["panier_order"];?>-toggle" style="cursor:pointer;" role="presentation"><a href="#"><?php echo $p["produit_nom"];?></a></li>
						<?php }
} ?>
					</ul>
					Total du panier : <span id="prix-total"><?php echo $prixTotal;?></span> €
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/nav-tabs.js"></script>
		<script>
			$(document).ready(function(){
				var listeAdherents = JSON.parse('<?php echo json_encode($array_eleves);?>');
				$("[name='identite_nom']").autocomplete({
					source: listeAdherents
				});
				$("#identite_nom").keyup(function(){
					ifAdherentExists();
				}).blur(function(){
					ifAdherentExists();
				})

				$("[name^='promotion']").keyup(function(){
					if($(this).val().length != '0'){
						$(this).prev().children().prop("checked", true);
						var lastChar = $(this).attr('name').substr($(this).attr('name').length - 1);
					} else {
						$(this).prev().children().prop("checked", false);
						var lastChar = $(this).attr('name').substr($(this).attr('name').length - 1);
					}
					calculatePrice(lastChar);
				})

				function calculatePrice(digit){
					var reductionEuros = $("[name='promotion-e-"+digit+"']").val();
					var reductionPourcent = $("[name='promotion-p-"+digit+"']").val();
					var prixInitial = $("#prix-fixe-"+digit+"").val();
					var prixReduit = prixInitial;
					if($("#promotion-euros-"+digit+"").prop("checked")){
						prixReduit = prixInitial - reductionEuros;
					} else if($("#promotion-pourcent-"+digit+"").prop("checked")){
						prixReduit = prixInitial - ((prixInitial * reductionPourcent)/100);
					}
					$("#prix-calcul-"+digit+"").val(prixReduit);

					var prixTotal = 0;
					$(":regex(id,^prix-calcul)").each(function(){
						var prixSeul = parseInt($(this).val());
						prixTotal += prixSeul;
					})
					$("#prix-total").html(prixTotal);
				}

				// Stockage du panier
				$("#check-memory").click(function(){
					sessionStorage.clear();
					var i = 1;
					for(i; i <= <?php echo $quantitePanier;?>; i++){
						sessionStorage.setItem("produit-"+i+"", $("#produit-title-"+i).html());
						sessionStorage.setItem("beneficiaire-"+i+"", $("#identite_nom-"+i).val());
						if($("#date_activation-"+i).val() != ""){
							sessionStorage.setItem("activation-"+i+"", $("#date_activation-"+i).val());
						} else {
							sessionStorage.setItem("activation-"+i+"", 0);
						}
						sessionStorage.setItem("prixIndividuel-"+i+"", $("#prix-calcul-"+i).val());
					}
					sessionStorage.setItem('numberProduits', i-1);
					sessionStorage.setItem('prixTotal', $("#prix-total").html());
				})
			})
			var listening = false;
			var wait;
			$("[name='fetch-rfid']").click(function(){
				if(!listening){
					wait = setInterval(function(){fetchRFID()}, 2000);
					$("[name='fetch-rfid']").html("Détection en cours...");
					listening = true;
				} else {
					clearInterval(wait);
					$("[name='fetch-rfid']").html("Lancer la détection");
					listening = false;
				}
			});
			function fetchRFID(){
				$.post('functions/fetch_rfid.php').done(function(data){
					if(data != ""){
						$("[name='rfid']").val(data);
						clearInterval(wait);
						$("[name='fetch-rfid']").html("Lancer la détection");
						listening = false;
					} else {
						console.log("Aucun RFID détecté");
					}
				});
			}
		</script>
	</body>
</html>
