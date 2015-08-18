<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET["id"];

$queryForfait = $db->prepare('SELECT *, produits_adherents.date_activation AS dateActivation FROM produits_adherents JOIN users ON id_adherent=users.user_id JOIN produits ON id_produit=produits.produit_id WHERE id_transaction=?');
$queryForfait->bindValue(1, $data);
$queryForfait->execute();
$forfait = $queryForfait->fetch(PDO::FETCH_ASSOC);

if($forfait["dateActivation"] == "0000-00-00 00:00:00"){
	$date_activation = "Activation en attente";
	$date_expiration = "Déterminée à l'activation";
} else {
	$date_activation = date_create($forfait["dateActivation"])->format('d/m/Y');
	$date_expiration = date_create($forfait["date_expiration"])->format('d/m/Y');
}

$queryCours = $db->prepare('SELECT * FROM cours_participants JOIN cours ON cours_id_foreign=cours.cours_id JOIN niveau ON cours_niveau=niveau.niveau_id JOIN salle ON cours_salle=salle.salle_id WHERE produit_adherent_id=?');
$queryCours->bindValue(1, $data);
$queryCours->execute();

$heuresCours = 0;
$dureeCours = 0;

$queryEcheances = $db->query("SELECT * FROM produits_echeances WHERE id_produit_adherent='$data'");
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forfait <?php echo $forfait["produit_nom"];?> de <?php echo $forfait["user_prenom"]." ".$forfait["user_nom"];?> | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="alert alert-success" id="hours-updated" style="display:none;">Nombre d'heures restantes mis à jour.</div>
           <div class="col-sm-10 main">
                <div class="btn-toolbar" id="top-page-buttons">
                   <a href="eleve_details.php?id=<?php echo $forfait["id_adherent"];?>" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à l'adhérent (<?php echo $forfait["user_prenom"]." ".$forfait["user_nom"];?>)</a>
                </div> <!-- btn-toolbar -->
               <h1 class="page-title"><span class="glyphicon glyphicon-credit-card"></span> Forfait <?php echo $forfait["produit_nom"];?> de <?php echo $forfait["user_prenom"]." ".$forfait["user_nom"]." (transaction ".$forfait["id_transaction"].")";?></h1>
			  <ul class="nav nav-tabs">
                   <li role="presentation" id="infos-toggle" class="active"><a>Détails du forfait</a></li>
                   <li role="presentation" id="history-toggle"><a>Liste des cours</a></li>
                   <li role="presentation" id="maturity-toggle"><a>Echéances</a></li>
               </ul>
              <section id="infos">
               <ul style="padding-left:0 !important;">
                    <li class="details-list">
                        <div class="col-sm-5 list-name">Nom du produit</div>
                        <div class="col-sm-7"><?php echo $forfait["produit_nom"];?></div>
                    </li>
                    <li class="details-list">
                        <div class="col-sm-5 list-name">Acheté le </div>
                        <div class="col-sm-7"><?php echo date_create($forfait["date_achat"])->format('d/m/Y');?></div>
                    </li>
                    <li class="details-list">
                        <div class="col-sm-5 list-name">Date d'activation</div>
                        <div class="col-sm-7"><strong><?php echo $date_activation;?></strong> pour <?php echo $forfait["validite_initiale"]/7;?> semaines</div>
                    </li>
                    <li class="details-list">
                        <div class="col-sm-5 list-name">Date d'expiration</div>
                        <div class="col-sm-7"><?php echo $date_expiration;?>
                        </div>
                    </li>
                    <li class="details-list">
                        <div class="col-sm-5 list-name">Volume de cours initial</div>
                        <div class="col-sm-7"><span id="initial-hours"><?php echo $forfait["volume_horaire"];?></span> heures</div>
                    </li>
                    <li class="details-list">
                        <div class="col-sm-5 list-name">Prix d'achat</div>
                        <div class="col-sm-7"><?php echo $forfait["prix_achat"];?> €</div>
                    </li>
                    <li class="details-list">
                        <div class="col-sm-5 list-name">AREP utilisable ?</div>
                        <div class="col-sm-7"><strong><?php echo $forfait["autorisation_report"]=="0"?"non":"oui";?></strong></div>
                    </li>
                    <li class="details-list">
                        <div class="col-sm-5 list-name">Volume de cours restant</div>
                        <div class="col-sm-7"><span id="remaining-hours"><?php echo $forfait["volume_cours"];?></span> heures <div class="btn-group" role="group"><button type="button" class="btn btn-info" onclick="calculateRemainingHours()"><span class="glyphicon glyphicon-scale"></span> Recalculer</button><button type="button" class="btn btn-primary" onclick="updateRemainingHours()"><span class="glyphicon glyphicon-save"></span> Valider le calcul</button></div></div>
                    </li>
              </ul>
               </section>
               <section id="history">
                   <table class="table table-striped">
                       <thead>
                           <tr>
                               <th>Intitulé</th>
                               <th>Jour</th>
                               <th>Niveau</th>
                               <th>Lieu</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php while($cours = $queryCours->fetch(PDO::FETCH_ASSOC)){ ?>
                               <tr>
                                   <td><?php echo $cours["cours_intitule"];?></td>
                                   <td>Le <?php echo date_create($cours["cours_start"])->format('d/m/Y \d\e H\hi');?> à <?php echo date_create($cours["cours_end"])->format('H\hi');?></td>
                                   <td><?php echo $cours["niveau_name"];?></td>
                                   <td><?php echo $cours["salle_name"];?></td>
                               </tr>
                           <?php $dureeCours = (strtotime($cours["cours_end"]) - strtotime($cours["cours_start"]))/3600;
                                $heuresCours += $dureeCours;} ?>
                       </tbody>
                       <input type="hidden" name="total-cours" value="<?php echo $heuresCours;?>">
                       <input type="hidden" name="id" value="<?php echo $data;?>">
                   </table>
               </section>
               <section id="maturity">
               	<table class="table">
               		<thead>
               			<tr>
               				<th>Date de l'échéance</th>
               				<th>Montant de l'échéance</th>
               				<th>Méthode de paiement</th>
               				<th>Statut</th>
               			</tr>
               		</thead>
               		<tbody>
               			<?php while($echeances = $queryEcheances->fetch(PDO::FETCH_ASSOC)){ 
							switch($echeances["echeance_effectuee"]){
								case 0:
									$status = "En attente";
									$statusClass = "default";
									break;
									
								case 1:
									$status = "Payée";
									$statusClass = "success";
									break;
									
								case 2:
									$status = "En retard";
									$statusClass = "danger";
									break;
							} ?>
               			<tr class="alert alert-<?php echo $statusClass;?>">
               				<td><?php echo date_create($echeances["date_echeance"])->format('d/m/Y');?></td>
               				<td><?php echo $echeances["montant"];?> €</td>
               				<td><?php echo $echeances["methode_paiement"];?></td>
               				<td><?php echo $status?></td>
               			</tr>
               			<?php } ?>
               		</tbody>
               	</table>
               </section>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script src="assets/js/nav-tabs.js"></script>
   <script>
       var remainingHours;
        function calculateRemainingHours(){
            window.remainingHours = $("#initial-hours").html() - $("*[name='total-cours']").val();
            $("#remaining-hours").html(window.remainingHours);
        }
       
       function updateRemainingHours(){
           var update_id = $("*[name=id]").val();
           var remainingHours = window.remainingHours;
           $.post("functions/update_volume_cours.php", {update_id, remainingHours}).done(function(data){
               $.notify("Modifications enregistrées.", {globalPosition: "bottom right", className:"success"});
           })
       }
    </script>
</body>
</html>