<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$queryAdherentsNom = $db->query("SELECT * FROM users ORDER BY user_nom ASC");
$array_eleves = array();
while($adherents = $queryAdherentsNom->fetch(PDO::FETCH_ASSOC)){
	array_push($array_eleves, $adherents["user_prenom"]." ".$adherents["user_nom"]);
}

$articlePanier = $_GET["element"];
$elementSepare = explode('-', $articlePanier);
$panierTotal = array();
for($z = 0; $z < sizeof($elementSepare); $z++){
	$elementPanier = $db->query("SELECT * FROM produits WHERE produit_id=$elementSepare[$z]")->fetch(PDO::FETCH_ASSOC);
	$key = array("key" => $z);
	$elementFull = array_merge($elementPanier, $key);
	array_push($panierTotal, $elementFull);
}

$prixTotal = 0;
$date_now = date_create("now")->format("Y-m-d");
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
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-shopping-cart"></span> Acheter des produits</p>
					</div>
					<div class="col-lg-6">
						<div class="btn-toolbar">
							<a href="catalogue.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> <span class="glyphicon glyphicon-th"></span> Retourner au catalogue</a>
						</div><!-- btn-toolbar -->
					</div>
				</div>
				<div class="col-sm-8 main" id="right-bordered">
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="25" aria-valuemax="100" style="width:50%;">
							<span class="glyphicon glyphicon-erase"></span> Etape 2/4 : Personnalisation des produits
						</div>
					</div>
					<?php foreach($panierTotal as $p){ ?>
					<section id="details-<?php echo $p["key"];?>">
						<p id="produit-title-<?php echo $p["key"];?>" class="produit-title"><?php echo $p["produit_nom"];?></p>
						<span role="button" class="input-group-btn">
							<a href="#produit-details-<?php echo $p["key"];?>" class="btn btn-default btn-block" data-toggle="collapse" aria-expanded="false"><span class="glyphicon glyphicon-search"></span> Détails...</a>
						</span>
						<div id="produit-details-<?php echo $p["key"];?>" class="collapse">
							<div id="produit-content" class="well">
								<?php if($p["produit_nom"]=="Invitation"){?>
								<p>Cette invitation est à usage unique. Si elle n'est pas liée à un cours, sa période de validité est alors de <?php echo $p["validite_initiale"];?> jours.</p>
								<?php } else { ?>
								<p>Cet abonnement est valable pendant <?php echo $p["validite_initiale"]/7;?> semaines.</p>
								<?php } ?>
								<p>Il donne accès à <?php echo $p["volume_horaire"];?> heures de cours pendant toute sa durée d'activation.</p>
								<p>L'extension de durée (AREP) n'est pas autorisée.</p>
								<input type="hidden" name="validite_produit-<?php echo $p["key"];?>" value="<?php echo $p["validite_initiale"];?>">
							</div>
						</div>
						<div class="form-group"> <!-- Bénéficiaire -->
							<label for="personne">Bénéficiaire</label>
							<input type="text" name="identite_nom-<?php echo $p["key"];?>" class="form-control has-check has-name-completion input-lg" placeholder="Nom">
							<p class="error-alert" id="err_adherent"></p>
							<div class="alert alert-danger" id="unpaid" style="display:none;"><strong>Cet adhérent a des échéances impayées. Impossible de continuer la procédure</strong></div>
						</div>
						<div id="maturities-checked">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="date_activation">Date d'activation <span class="label-tip">Par défaut : activation au premier passage</span></label>
										<div class="input-group">
											<?php if(stristr($p["produit_nom"], "adhésion")){ ?>
											<input type="date" name="date_activation" id="date_activation-<?php echo $p["key"];?>" class="form-control" onchange="showExpDate(<?php echo $p["key"];?>)" value="<?php echo $date_now;?>">
											<?php } else { ?>
											<input type="date" name="date_activation" id="date_activation-<?php echo $p["key"];?>" class="form-control" onchange="showExpDate(<?php echo $p["key"];?>)">
											<?php } ?>
											<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" date-today="true" onclick="showExpDate(<?php echo $p["key"];?>)">Insérer aujourd'hui</a></span>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="date_expiration">Date indicative d'expiration</label>
										<div class="input-group">
											<input type="date" name="date_expiration-<?php echo $p["key"];?>" class="form-control">
											<span role="button" class="input-group-btn"><a class="btn btn-info" role="button" onclick="showExpDate(<?php echo $p["key"];?>)">Rafraîchir</a></span>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="promotion-e">Réduction (en €)</label>
										<div class="input-group">
											<span class="input-group-addon"><input type="radio" id="promotion-euros-<?php echo $p["key"];?>" name="promotion" class="checkbox-x">Réduction en €</span>
											<input type="number" name="promotion-e-<?php echo $p["key"];?>" class="form-control">
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="promotion-p">Réduction (en %)</label>
										<div class="input-group">
											<span class="input-group-addon"><input type="radio" name="promotion" id="promotion-pourcent-<?php echo $p["key"];?>">Réduction en %</span>
											<input type="number" name="promotion-p-<?php echo $p["key"];?>" class="form-control">
										</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="prix_achat">Montant</label>
								<div class="input-group">
									<span class="input-group-addon">€</span>
									<input type="hidden" id="prix-fixe-<?php echo $p["key"];?>" value="<?php echo $p["tarif_global"];?>">
									<input type="number" name="prix_achat" id="prix-calcul-<?php echo $p["key"];?>" class="form-control prix-display" value="<?php echo $p["tarif_global"];?>">
								</div>
							</div>
						</div>
					</section>
					<?php $prixTotal += $p["tarif_global"];
													 } ?>
				</div>
				<div class="col-sm-2 shopping-section">
					<h4><span class="glyphicon glyphicon-shopping-cart"></span> Panier en cours</h4>
					<ul class="nav nav-pills nav-stacked">
						<?php foreach($panierTotal as $p){
	if($p === reset($panierTotal)) {?>
						<li id="details-<?php echo $p["key"];?>-toggle" class="active" style="cursor:pointer;" role="presentation"><a href="#"><?php echo $p["produit_nom"];?></a></li>
						<?php } else { ?>
						<li id="details-<?php echo $p["key"];?>-toggle" style="cursor:pointer;" role="presentation"><a href="#"><?php echo $p["produit_nom"];?></a></li>
						<?php }
} ?>
					</ul>
					<p id="shopping-cart-price">Total du panier : <span id="prix-total"><?php echo $prixTotal;?></span> €</p>
					<a role="button" id="check-memory" class="btn btn-success btn-block"> Règlement des achats <span class="glyphicon glyphicon-arrow-right"></span></a>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/nav-tabs.js"></script>
		<script>
			$(document).ready(function(){
				var listeAdherents = JSON.parse('<?php echo json_encode($array_eleves);?>');
				$(".has-name-completion").autocomplete({
					source: listeAdherents,
					select: function(event, ui){
						$(":regex(id,^unknown-user)").remove();
						$(".has-name-completion").val(this.value);
					},
					change : function(event, ui){
						sessionStorage.setItem('beneficiaire-principal', $(".has-name-completion").val());
					}
				});

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
						var prixSeul = parseFloat($(this).val());
						prixTotal += prixSeul;
					})
					$("#prix-total").html(prixTotal);
				}

				// Stockage du panier
				$("#check-memory").click(function(){
					var i = 1;
					for(i; i <= 20; i++){
						var j = i-1;
						if(sessionStorage.getItem('produit_id-'+i) != null){
							sessionStorage.setItem("produit-"+i+"", $("#produit-title-"+j).html());
							sessionStorage.setItem("beneficiaire-"+i+"", $("[name='identite_nom-"+j+"']").val());
							sessionStorage.setItem("activation-"+i+"", $("#date_activation-"+j).val());
							sessionStorage.setItem("prixIndividuel-"+i+"", $("#prix-calcul-"+j).val());
						}
					}
					sessionStorage.setItem('prixTotal', $("#prix-total").html());
					var url = "paiement.php";
					window.location = url;
				})
			})
			function showExpDate(digit){
				var date_activation = new moment($("[name='date_activation-"+digit+"']").val());
				var validite = $("[name='validite_produit-"+digit+"']").val();
				var date_desactivation = date_activation.add(validite, 'days').format('YYYY-MM-DD');
				console.log(validite);
				$("[name='date_expiration-"+digit+"']").val(date_desactivation);
			}
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
