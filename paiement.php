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
				<div class="col-sm-10 main">
					<h1 class="page-title">Acheter des produits</h1>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="25" aria-valuemax="100" style="width:75%;">
							<span class="glyphicon glyphicon-repeat"></span> Etape 3/4 : Ajustement des échéances
						</div>
					</div>
					<form action="paiement.php" method="post">
						<div class="btn-toolbar">
							<a href="personnalisation.php" role="button" class="btn btn-default" name="previous"><span class="glyphicon glyphicon-arrow-left"></span> <span class="glyphicon glyphicon-erase"></span> Retourner à la personnalisation des abonnements</a>
							<input type="submit" role="button" class="btn btn-primary" name="submit" value="PROCEDER">
						</div> <!-- btn-toolbar -->
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
						<div class="form-group">
							<label for="prix_total">Prix total de la commande</label>
							<input type="text" name="prix_total" class="form-control">
						</div>
						<div class="form-group">
							<label for="payeur">Payeur</label>
							<input type="text" name="payeur" class="form-control" placeholder="Nom">
						</div>
						<div class="form-group">
							<label for="echeances">Nombre d'échéances mensuelles</label>
							<input type="text" name="echeances" class="form-control">
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
						<input type="submit" role="button" class="btn btn-primary btn-block" name="submit" value="PROCEDER">
					</form>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var listeAdherents = JSON.parse('<?php echo json_encode($array_eleves);?>');
				$("[name='payeur']").autocomplete({
					source: listeAdherents
				});
				var i = 1;
				var recap;
				for(i; i <= 20; i++){
					if(sessionStorage.getItem('produit_id-'+i) != null){
						recap += "<tr>";
						recap += "<td><input type='hidden' class='form-control' value='"+sessionStorage.getItem('produit-'+i)+"' name='nom-produit-"+i+"'>"+sessionStorage.getItem('produit-'+i)+"</td>";
						recap += "<td><input type='hidden' class='form-control' value='"+sessionStorage.getItem('beneficiaire-'+i)+"' name='beneficiaire-"+i+"'>"+sessionStorage.getItem('beneficiaire-'+i)+"</td>";
						if(sessionStorage.getItem('activation-'+i) == '0'){
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
					if(start_date.date() < 8){
						start_date.date(10);
					} else if(start_date.date() < 18){
						start_date.date(20);
					} else if(start_date.date() < 28){
						start_date.date(30);
					} else {
						start_date.date(41);
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
						var current_date = start_date.add(1, 'month').format("YYYY-MM-DD");
						echeance += "<td class='col-lg-1'><input type='date' class='form-control' value="+current_date+" name='date-echeance-"+i+"'></td>";
						echeance += "<td class='col-lg-2'><div class='input-group'><input type='text' class='form-control' placeholder='Montant' value="+montant_echeance+" name='montant-echeance-"+i+"'><span class='input-group-addon'>€</span></div></td>";
						echeance += "<td class='col-lg-4'><div class='input-group'><input type='text' class='form-control' name='moyen-paiement-"+i+"' placeholder='CB / Numéro de chèque / Mandat / Espèces...'><span role='buttton' class='input-group-btn'><a class='btn btn-info' role='button' name='propagation-methode-"+i+"'>Propager</a></span></div></td>";
						echeance += "<td class='col-lg-4'><div class='input-group'><input type='text' class='form-control' name='titulaire-paiement-"+i+"' placeholder='Prénom Nom'><span role='button' class='input-group-btn'><a class='btn btn-info' role='button' name='propagation-titulaire-"+i+"'>Propager</a></span></div></td>";
						echeance += "<td class='col-lg-1'><input name='statut-echeance-"+i+"'></td>";
						echeance += "</tr>";
						montant_restant -= montant_echeance;
						$(".maturities-table").append(echeance);
					}
					$("[name^='montant-echeance']").keyup(function(){
						// Lorsqu'un montant est modifié.
						var echeance_fixe = $(this).val();
						if(echeance_fixe != ''){
							$(this).addClass('fixed');
						} else {
							$(this).removeClass('fixed');
						}

						var montant_restant_auto = montant_total;
						$(".fixed").each(function(){
							montant_restant_auto -= $(this).val();
						})
						montant_restant = montant_restant_auto;
						var echeances_fixees = $(".fixed").length;
						var echeances_auto = $("[name^='montant-echeance']:not(.fixed)").length;
						i = 0;
						$("[name^='montant-echeance']:not(.fixed)").each(function(){
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
					$("[name^='propagation-methode']").click(function(){
						var clicked = $(this);
						var methode = clicked.parent().prev().val();
						if(methode.indexOf("Chèque") != -1){
							var token = "Chèque n°";
							$("[name^='propagation-methode']").parent().prev().val(token);
							clicked.parent().prev().val(methode);
						} else {
							$("[name^='propagation-methode']").parent().prev().val(methode);
						}

					})
					$("[name^='propagation-titulaire']").click(function(){
						var titulaire = $(this).parent().prev().val();
						console.log(titulaire);
						$("[name^='propagation-titulaire']").parent().prev().val(titulaire);
					});
				})
			})
		</script>
		<script>
			$(document).ready(function(){
				composeURL();
			});
		</script>
	</body>
</html>
