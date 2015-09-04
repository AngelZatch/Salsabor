<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
include 'functions/ventes.php';

$queryAdherentsNom = $db->query("SELECT * FROM users ORDER BY user_nom ASC");
$array_eleves = array();
while($adherents = $queryAdherentsNom->fetch(PDO::FETCH_ASSOC)){
	array_push($array_eleves, $adherents["user_prenom"]." ".$adherents["user_nom"]);
}

if(isset($_POST["submit"])){
	vente();
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Récapitulatif de commande | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<form action="paiement.php" method="post">
					<div class="fixed">
						<div class="col-lg-6">
							<p class="page-title"><span class="glyphicon glyphicon-shopping-cart"></span> Acheter des produits</p>
						</div>
						<div class="col-lg-6">
							<div class="btn-toolbar">
								<a href="personnalisation.php" role="button" class="btn btn-default" name="previous"><span class="glyphicon glyphicon-arrow-left"></span> <span class="glyphicon glyphicon-erase"></span> Retourner à la personnalisation des abonnements</a>
								<a href="actions/validate_paiement.php" role="button" class="btn btn-primary" data-title="Validation du panier" data-toggle="lightbox" data-gallery="remoteload">PROCEDER</a>
							</div> <!-- btn-toolbar -->
						</div>
					</div>
					<div class="col-sm-10 main">
						<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="25" aria-valuemax="100" style="width:100%;">
								<span class="glyphicon glyphicon-repeat"></span> Etape 3/3 : Ajustement des échéances
							</div>
						</div>
						<p>Récapitulatif de la commande</p>
						<table class="table">
							<thead>
								<tr>
									<th>Produit</th>
									<th>Bénéficiaire</th>
									<th>Activation</th>
									<th>Prix</th>
								</tr>
							</thead>
							<tbody class="produits-recap">
							</tbody>
						</table>
						<div class="row">
							<div class="col-lg-2">
								<div class="form-group">
									<label for="prix_total">Prix total</label>
									<div class="input-group">
										<input type="number" step="any" name="prix_total" class="form-control input-lg">
										<span class="input-group-addon">€</span>
									</div>
								</div>
							</div>
							<div class="col-lg-2">
								<div class="form-group">
									<label for="echeances">Nombre d'échéances</label>
									<input type="number" name="echeances" class="form-control input-lg">
								</div>
							</div>
							<div class="col-lg-8">
								<div class="form-group">
									<label for="payeur">Payeur</label>
									<input type="text" name="payeur" class="form-control has-check mandatory has-name-completion input-lg" placeholder="Nom">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="numero_echeance">Détail des échances</label>
							<table class="table table-striped">
								<thead>
									<tr>
										<th class="col-lg-1">Date de l'échéance</th>
										<th class="col-lg-2">Montant</th>
										<th class="col-lg-4">Méthode de règlement</th>
										<th class="col-lg-4">Titulaire du moyen de paiement</th>
										<th class="col-lg-1">Déjà reçue ?</th>
									</tr>
								</thead>
								<tbody class="maturities-table">
								</tbody>
							</table>
						</div>
						<input type="hidden" name="nombre_produits">
						<a href="actions/validate_paiement.php" role="button" class="btn btn-primary btn-block" data-title="Validation du panier" data-toggle="lightbox" data-gallery="remoteload">PROCEDER</a>
						<input type="submit" style="display:none;" class="submit-relay-target" name="submit">
					</div>
				</form>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var listeAdherents = JSON.parse('<?php echo json_encode($array_eleves);?>');
				$(".has-name-completion").autocomplete({
					source: listeAdherents
				});
				var i = 1;
				$(":regex(name,payeur)").val(sessionStorage.getItem('beneficiaire-principal'));
				var recap;
				for(i; i <= 20; i++){
					if(sessionStorage.getItem('produit_id-'+i) != null){
						recap += "<tr>";
						recap += "<td><input type='hidden' class='form-control' value='"+sessionStorage.getItem('produit-'+i)+"' name='nom-produit-"+i+"'>"+sessionStorage.getItem('produit-'+i)+"</td>";
						recap += "<input type='hidden' class='form-control' value='"+sessionStorage.getItem('produit_id-'+i)+"' name='produit_id-"+i+"'>";
						recap += "<td><input type='hidden' class='form-control' value='"+sessionStorage.getItem('beneficiaire-'+i)+"' name='beneficiaire-"+i+"'>"+sessionStorage.getItem('beneficiaire-'+i)+"</td>";
						if(sessionStorage.getItem('activation-'+i) == ""){
							recap += "<td><input type='hidden' name='activation-"+i+"' value='0'>Activation automatique</td>";
						} else {
							recap += "<td><input type='hidden' name='activation-"+i+"' value='"+sessionStorage.getItem('activation-'+i)+"'>"+sessionStorage.getItem('activation-'+i)+"</td>";
						}
						recap += "<td><input type='hidden' class='form-control' value="+sessionStorage.getItem('prixIndividuel-'+i)+" name='prix-produit-"+i+"'>"+sessionStorage.getItem('prixIndividuel-'+i)+" €</td>";
						recap += "</tr>";
					}
				}
				$(".produits-recap").append(recap);
				var prixTotal = sessionStorage.getItem('prixTotal');
				$("[name='prix_total']").val(prixTotal);
				$("[name='nombre_produits']").val(numberProduits - 1);
				var methods = [
					"Carte bancaire",
					"Chèque n°",
					"Espèces",
					"Virement compte à compte",
					"Chèques vacances",
					"En attente"
				];
				// Gestion des échéances (nombre et valeur)
				$("[name='echeances']").keyup(function(){
					var nbEcheances = $(this).val();
					var i = 1;
					var start_date = moment();
					if(start_date.date() >= 1 && start_date.date() < 8){
						start_date.date(10);
					} else if(start_date() >= 9 && start_date.date() < 18){
						start_date.date(20);
					} else if(start_date.date() >= 19 && start_date.date() < 28){
						start_date.date(30);
					} else {
						var month = start_date.month();
						start_date.month(month).date(10);
					}
					var montant_total = prixTotal;
					var montant_restant = montant_total;
					if(montant_total != ''){
						var montant_echeance = (montant_total/nbEcheances).toFixed(2);
					}
					$(".maturities-table").empty();
					for(i; i <= nbEcheances; i++){
						if(i == nbEcheances){
							montant_echeance = montant_restant;
						}
						// Construction du tableau des échéances
						var echeance = "<tr>";
						var current_date = start_date.format("YYYY-MM-DD");
						echeance += "<td class='col-lg-1'><div class='input-group'><input type='date' class='form-control' value="+current_date+" name='date-echeance-"+i+"'><span role='button' class='input-group-btn'><a class='btn btn-info' role='button' name='propagation-date-"+i+"'>Propager</a></span></div></td>";
						echeance += "<td class='col-lg-2'><div class='input-group'><input type='number' class='form-control' placeholder='Montant' value="+montant_echeance+" name='montant-echeance-"+i+"'><span class='input-group-addon'>€</span></div></td>";
						echeance += "<td class='col-lg-4'><div class='input-group'><input type='text' class='form-control' name='moyen-paiement-"+i+"' placeholder='En attente / Carte bancaire / Numéro de chèque / Mandat / Espèces...'><span role='buttton' class='input-group-btn'><a class='btn btn-info' role='button' name='propagation-methode-"+i+"'>Propager</a></span></div></td>";
						echeance += "<td class='col-lg-4'><div class='input-group'><input type='text' class='form-control' name='titulaire-paiement-"+i+"' placeholder='Prénom Nom' value='"+$(":regex(name,payeur)").val()+"'><span role='button' class='input-group-btn'><a class='btn btn-info' role='button' name='propagation-titulaire-"+i+"'>Propager</a></span></div></td>";
						echeance += "<td class='col-lg-1'><input name='statut-echeance-"+i+"'></td>";
						echeance += "</tr>";
						montant_restant -= montant_echeance;
						$(".maturities-table").append(echeance);
						start_date.add(1, 'month').format("YYYY-MM-DD");
					}
					$("[name^='montant-echeance']").keyup(function(){
						// Lorsqu'un montant est modifié.
						var echeance_fixe = $(this).val();
						if(echeance_fixe != ''){
							$(this).addClass('fixed-value');
						} else {
							$(this).removeClass('fixed-value');
						}

						var montant_restant_auto = montant_total;
						$(".fixed-value").each(function(){
							montant_restant_auto -= $(this).val();
						})
						montant_restant = montant_restant_auto;
						var echeances_fixees = $(".fixed-value").length;
						var echeances_auto = $("[name^='montant-echeance']:not(.fixed-value)").length;
						i = 0;
						$("[name^='montant-echeance']:not(.fixed-value)").each(function(){
							if(i == echeances_auto - 1){
								montant_echeance = (montant_restant).toFixed(2);
							} else {
								montant_echeance = (montant_restant_auto/echeances_auto).toFixed(2);
							}
							$(this).val(montant_echeance);
							montant_restant -= montant_echeance;
							i++;
						})
					})
					$("[name^='moyen-paiement']").autocomplete({
						source: methods
					})
					$("[name^='statut-echeance']").checkboxX({threeState: false, size: 'lg', value: 0});
					$(":regex(name,^propagation-date)").click(function(){
						var date = $(this).parent().prev().val();
						var indice = $(this).attr('name').substr(17);
						for(var m = indice++; m <= nbEcheances; m++){
							$(":regex(name,date-echeance-"+m+")").val(date);
							date = moment(date).add(1, 'month').format('YYYY-MM-DD');
						}
					})
					$("[name^='propagation-methode']").click(function(){
						var clicked = $(this);
						var methode = clicked.parent().prev().val();
						console.log(methode);
						var indice = $(this).attr('name').substr(20);
						if(methode.indexOf("Chèque") != -1 && methode != "Chèques vacances"){
							var token = "Chèque n°";
							var numero = methode.substr(9);
							for(var m = indice++; m <= nbEcheances; m++){
								$("[name='propagation-methode-"+m+"']").parent().prev().val(token+""+numero);
								numero++;
							}
							clicked.parent().prev().val(methode);
						} else {
							for(var m = indice++; m <= nbEcheances; m++){
								$("[name^='propagation-methode-"+m+"']").parent().prev().val(methode);
							}
						}
					})
					$("[name^='propagation-titulaire']").click(function(){
						var titulaire = $(this).parent().prev().val();
						console.log(titulaire);
						$("[name^='propagation-titulaire']").parent().prev().val(titulaire);
					});
				})
				$(".mandatory").change();
			})
		</script>
		<script>
			$(document).ready(function(){
				composeURL();
			});
		</script>
	</body>
</html>
