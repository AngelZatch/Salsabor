<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
include 'functions/ventes.php';

$invitation = $db->query("SELECT * FROM produits WHERE produit_nom='Invitation'")->fetch(PDO::FETCH_ASSOC);

$queryAdherentsNom = $db->query("SELECT * FROM users ORDER BY user_nom ASC");
$array_eleves = array();
while($adherents = $queryAdherentsNom->fetch(PDO::FETCH_ASSOC)){
	array_push($array_eleves, $adherents["user_prenom"]." ".$adherents["user_nom"]);
}

$date_activation = date_create("now")->format("Y-m-d");
$date_expiration = date("Y-m-d", strtotime($date_activation.'+'.$invitation["validite_initiale"].'DAYS'));

$coursAVenir = $db->query("SELECT * FROM cours WHERE cours_start >= '$date_activation 00:00:00' ORDER BY cours_start ASC");

if(isset($_POST["submit"])){
	invitation();
}
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
				<form action="" method="post" target="_blank">
					<div class="fixed">
						<div class="col-lg-6">
							<p class="page-title"><span class="glyphicon glyphicon-heart-empty"></span> Inviter un élève</p>
						</div>
						<div class="col-lg-6">
							<div class="btn-toolbar">
								<a href="dashboard.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Annuler et retourner au panneau d'administration</a>
								<input type="submit" name="submit" role="button" class="btn btn-primary" value="ENREGISTRER">
							</div> <!-- btn-toolbar -->
						</div>
					</div>
					<div class="col-sm-10 main">
						<input type="hidden" value="<?php echo $invitation["produit_id"]?>" class="form-control" id="produit-select" name="produit">
						<div class="form-group">
							<label for="personne">Acheteur du forfait</label>
							<input type="text" name="identite_nom" id="identite_nom" class="form-control has-check has-name-completion input-lg" placeholder="Nom">
							<p class="error-alert" id="err_adherent"></p>
						</div>
						<div class="form-group" id="association">
							<label for="cours">Associer un cours ? <span class="label-tip">L'invitation sera alors restreinte à ce cours et seulement celui-ci</span></label>
							<div class="input-group input-group-lg">
								<input type="text" name="cours" class="form-control" id="search" placeholder="Tapez pour filtrer">
								<span class="input-group-btn"><a href="#liste-cours" class="btn btn-default" data-toggle="collapse" aria-expanded="false" id="open-liste-cours">Liste des cours à venir</a></span>
							</div>
							<div class="collapse" id="liste-cours">
								<div class="well">
									<table class="table">
										<thead>
											<tr>
												<th>Liste des cours à venir</th>
											</tr>
										</thead>
										<tbody id="filter-enabled">
											<?php while($listeCours = $coursAVenir->fetch(PDO::FETCH_ASSOC)){ ?>
											<tr class="associable" value="<?php echo $listeCours["cours_id"];?>" style="cursor:pointer;">
												<td><?php echo $listeCours["cours_intitule"]." de ".$listeCours["cours_start"]." à ".$listeCours["cours_end"];?></td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
							</div>
							<input type="hidden" name="id-cours">
						</div>
						<div id="unassociated-invitation" class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="date_activation">Date d'activation</label>
									<input type="date" name="date_activation" class="form-control input-lg" value="<?php echo $date_activation?>">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label for="date_expiration">Date prévue d'expiration</label>
									<input type="date" name="date_expiration" class="form-control input-lg" value="<?php echo $date_expiration;?>">
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var listeAdherents = JSON.parse('<?php echo json_encode($array_eleves);?>');
				$("[name='identite_nom']").autocomplete({
					source: listeAdherents
				});

				$("#association").keyup(function(){
					if($("#search").val() != ''){
						if(!$(".collapse").hasClass('in')){
							$("#open-liste-cours").click();
						}
						$("#unassociated-invitation").hide();
					}
				})

				$(".associable").click(function(){
					$("#search").val($(this).children("td").html());
					$("[name=id-cours]").val($(this).attr("value"));
					$("#open-liste-cours").click();
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
