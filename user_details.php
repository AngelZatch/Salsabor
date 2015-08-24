<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];
$status = $_GET['status'];

// On obtient les détails de l'adhérent
$queryDetails = $db->prepare('SELECT * FROM users WHERE user_id=?');
$queryDetails->bindValue(1, $data);
$queryDetails->execute();
$details = $queryDetails->fetch(PDO::FETCH_ASSOC);

// Si l'élève est un professeur
if($details["est_professeur"] == 1){
    // On obtient l'historique de ses cours
    $queryHistoryDonnes = $db->prepare('SELECT * FROM cours JOIN niveau ON cours_niveau=niveau.niveau_id JOIN salle ON cours_salle=salle.salle_id WHERE prof_principal=? OR prof_remplacant=? ORDER BY cours_start ASC');
    $queryHistoryDonnes->bindValue(1, $data);
    $queryHistoryDonnes->bindValue(2, $data);
    $queryHistoryDonnes->execute();

    // Tarifs
    $queryTarifs = $db->prepare('SELECT * FROM tarifs_professeurs JOIN prestations ON type_prestation=prestations.prestations_id WHERE prof_id_foreign=?');
    $queryTarifs->bindValue(1, $data);
    $queryTarifs->execute();

    // Prestations
    $queryPrestations = $db->query('SELECT * FROM prestations WHERE est_cours=1');

    // Types de ratio multiplicatif
    $ratio = $db->query("SHOW COLUMNS FROM tarifs_professeurs LIKE 'ratio_multiplicatif'");
    
    // Prix de tous les cours
    $totalPrice = 0;
    $totalPaid = 0;
    $totalDue = 0;
}

if($details["est_membre"] == 1){
    // On obtient l'historique de ses cours
    $queryHistoryRecus = $db->prepare('SELECT * FROM cours_participants JOIN cours ON cours_id_foreign=cours.cours_id JOIN niveau ON cours.cours_niveau=niveau.niveau_id JOIN salle ON cours.cours_salle=salle.salle_id WHERE eleve_id_foreign=?');
    $queryHistoryRecus->bindValue(1, $data);
    $queryHistoryRecus->execute();

    // On obtient l'historique de ses réservations
    $queryResa = $db->prepare('SELECT * FROM reservations JOIN users ON reservation_personne=users.user_id JOIN prestations ON type_prestation=prestations_id JOIN salle ON reservation_salle=salle.salle_id WHERE reservation_personne=?');
    $queryResa->bindValue(1, $data);
    $queryResa->execute();

    // On obtient l'historique de ses forfaits
    $queryForfaits = $db->prepare('SELECT *, produits_adherents.date_activation AS dateActivation, produits_adherents.actif AS produitActif FROM produits_adherents JOIN users ON id_adherent=users.user_id JOIN produits ON id_produit=produits.produit_id WHERE id_adherent=? ORDER BY produitActif DESC, date_achat ASC');
    $queryForfaits->bindValue(1, $data);
    $queryForfaits->execute();

    // Ainsi que les forfaits actifs
    $queryForfaitsActifs = $db->prepare("SELECT * FROM produits_adherents JOIN produits ON id_produit=produits.produit_id WHERE id_adherent=? AND produits_adherents.actif=1");
    $queryForfaitsActifs->bindParam(1, $data);
    $queryForfaitsActifs->execute();

    // Et on cherche à savoir si des échéances sont en retard
    $queryEcheances = $db->query("SELECT * FROM produits_echeances JOIN produits_adherents ON id_produit_adherent=produits_adherents.id_transaction WHERE echeance_effectuee=2 AND id_adherent=$data")->rowCount();   
}

// Edit des informations
if(isset($_POST["edit"])){
	// Upload de l'image
	if(isset($_FILES["photo_identite"]["name"])){
		$target_dir = "assets/pictures/";
		$target_file = $target_dir.basename($_FILES["photo_identite"]["name"]);
		$uploadOk = 1;
		//$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
		/**$check = getimagesize($_FILES['photo_identite']['tmp_name']);
		if(!$check){
			$uploadOk = 1;
		} else {
			echo "Fichier non conforme";
			$uploadOk = 0;
		}**/
		if($uploadOk == 1){
			if(move_uploaded_file($_FILES["photo_identite"]["tmp_name"], $target_file)){
				echo "Fichier uploadé avec succès.";
			} else {
				echo "Erreur au transfert du fichier.";
			}
		}
	}
	
	try{
		$db->beginTransaction();
		$edit = $db->prepare('UPDATE users SET user_prenom = :prenom,
													user_nom = :nom,
													user_rfid = :rfid,
													date_naissance = :date_naissance,
													rue = :rue,
													code_postal = :code_postal,
													ville = :ville,
													mail = :mail,
													telephone = :telephone,
													photo = :photo
													WHERE user_id = :id');
		$edit->bindParam(':prenom', $_POST["identite_prenom"]);
		$edit->bindParam(':nom', $_POST["identite_nom"]);
		$edit->bindParam(':rfid', $_POST["rfid"]);
		$edit->bindParam(':date_naissance', $_POST["date_naissance"]);
		$edit->bindParam(':rue', $_POST["rue"]);
		$edit->bindParam(':code_postal', $_POST["code_postal"]);
		$edit->bindParam(':ville', $_POST["ville"]);
		$edit->bindParam(':mail', $_POST["mail"]);
		$edit->bindParam(':telephone', $_POST["telephone"]);
		$edit->bindParam(':photo', $target_file);
		$edit->bindParam(':id', $data);
		$edit->execute();
		if(isset($_POST["rfid"])){
			$delete = $db->prepare('DELETE FROM passages WHERE passage_eleve=? AND status=1');
			$delete->bindParam(1, $_POST["rfid"]);
			$delete->execute();
		}
		$db->commit();
		header("Location:user_details.php?id=$data");
	} catch(PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editer - <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
				<div class="btn-toolbar" id="top-page-buttons">
                  <?php if($status == 'professeur'){ ?>
                  <a href="professeurs.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à la liste des professeurs</a>
					<?php } if($status == 'membre'){ ?>
                   <a href="adherents.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à la liste des adhérents</a>
                   <?php } if($status == 'staff'){ ?>
					<a href="staff_liste.php?rank=0" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à la liste du staff</a>
               <?php } ?>
                </div> <!-- btn-toolbar -->
               <h1 class="page-title"><span class="glyphicon glyphicon-user"></span> <?php echo $details["user_prenom"]." ".$details["user_nom"];?></h1>
               <?php if($details["est_membre"] == 1 && $queryEcheances != 0){ ?>
               <div class="alert alert-danger"><strong>Attention !</strong> Cet adhérent a des échéances en retard.</div>
               <?php } ?>
			  <ul class="nav nav-tabs">
                   <li role="presentation" id="infos-toggle" class="active"><a>Informations personnelles</a></li>
                   <?php if($details["est_membre"] == 1){ ?>
                   <li role="presentation" id="history-suivis-toggle"><a>Cours suivis</a></li>
                   <li role="presentation" id="resa-toggle"><a>Réservations</a></li>
                   <li role="presentation" id="forfaits-toggle"><a>Forfaits/Abonnements</a></li>
                    <?php } if($details["est_professeur"] == 1){ ?>
                   <li role="presentation" id="history-donnes-toggle"><a>Cours donnés</a></li>
                   <li role="presentation" id="tarifs-toggle"><a>Tarifs</a></li>
                   <li role="presentation" id="stats-toggle"><a>Statistiques</a></li>
                   <?php } ?>
               </ul>
               <section id="infos">
               		<form method="post" role="form" enctype="multipart/form-data">
						<div class="container-fluid">
               				<div class="form-group col-sm-2 thumbnail" id="picture-container">
               					<label for="photo_identite">
								<img src="<?php echo ($details["photo"])?$details["photo"]:"assets/images/logotype-white.png";?>" alt="" style="max-height:100%; max-width:100%;">
								</label>
								<input type="file" name="photo_identite">
							</div>
							<div class="form-group col-sm-10">
								<label for="identite_prenom" class="control-label">Prénom</label>
								<input type="text" name="identite_prenom" id="identite_prenom" class="form-control" placeholder="Prénom" value="<?php echo $details["user_prenom"];?>">
							</div>
							<div class="form-group col-sm-10">
							<label for="identite_nom" class="control-label">Nom</label>
								<input type="text" name="identite_nom" id="identite_nom" class="form-control" placeholder="Nom" value="<?php echo $details["user_nom"];?>">
							</div>
							<div class="form-group col-sm-10">
								<label for="mail" class="control-label">Adresse mail</label>
								<input type="text" name="mail" id="mail" placeholder="Adresse mail" class="form-control" value="<?php echo $details["mail"];?>">
							</div>
               			</div>
               			<div class="form-group">
               				<label for="rfid" class="control-label">Code carte</label>
               				<div class="input-group">
               					<input type="text" name="rfid" class="form-control" placeholder="Scannez une nouvelle puce pour récupérer le code RFID" value="<?php echo $details["user_rfid"];?>">
               					<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" name="fetch-rfid">Lancer la détection</a></span>
               				</div>
						</div>
						<div class="form-group">
						<label for="" class="control-label">Adresse postale</label>
							<input type="text" name="rue" id="rue" placeholder="Adresse" class="form-control" value="<?php echo $details["rue"];?>">
						</div>
						<div class="form-group">
							<input type="text" name="code_postal" id="code_postal" placeholder="Code Postal" class="form-control" value="<?php echo $details["code_postal"];?>">
						</div>
						<div class="form-group">
							<input type="text" name="ville" id="ville" placeholder="Ville" class="form-control" value="<?php echo $details["ville"];?>">
						</div>
						<div class="form-group">
						<label for="telephone" class="control-label">Téléphone</label>
							<input type="text" name="telephone" id="telephone" placeholder="Numéro de téléphone" class="form-control" value="<?php echo $details["telephone"];?>">
						</div>
						<div class="form-group">
							<label for="date_naissance" class="control-label">Date de naissance</label>
							<input type="date" name="date_naissance" id="date_naissance" class="form-control" value=<?php echo $details["date_naissance"];?>>
						</div>
						<div class="form-group">
							<label for="certificat_medical" class="control-label">Certificat Médical</label>
							<input type="file" class="form-control">
						</div>
					  <input type="submit" name="edit" role="button" class="btn btn-primary btn-block" value="ENREGISTRER LES MODIFICATIONS">
					  </form>
               </section>
               <?php if($details["est_membre"] == 1){ ?>
               <section id="history-suivis">
               	<table class="table table-striped">
               		<thead>
               			<tr>
               				<th class="col-lg-2">Intitulé</th>
               				<th class="col-lg-3">Jour</th>
               				<th class="col-lg-2">Détails</th>
               				<th class="col-lg-3">Forfait</th>
               				<th class="col-lg-2">Prix pondéré</th>
               			</tr>
               		</thead>
               		<tbody>
               		<?php while($history = $queryHistoryRecus->fetch(PDO::FETCH_ASSOC)){ ?>
               			<tr <?php echo ($history["produit_adherent_id"]==null)?"class='warning'":"";?>>
               				<td class="col-lg-2"><?php echo $history['cours_intitule']." ".$history['cours_suffixe'];?></td>
               				<td class="col-lg-3"><?php echo date_create($history['cours_start'])->format('d/m/Y H:i');?> - <?php echo date_create($history['cours_end'])->format('H:i');?></td>
               				<td class="col-lg-2"><?php echo $history['niveau_name']."\n".$history['salle_name'];?></td>
               				<td class="col-lg-3">
               					<?php if($history["produit_adherent_id"]==null){?>
               					<button class="btn btn-info" name="link-forfait"><span class="glyphicon glyphicon-link"></span> Associer un forfait</button>
               					<input type="hidden" name="cours" value="<?php echo $history["cours_id"];?>">
               					<select name="forfaits-actifs" style="display:none;" class="form-control">
               						<?php while($forfaitsActifs = $queryForfaitsActifs->fetch(PDO::FETCH_ASSOC)){?>
										<option value="<?php echo $forfaitsActifs["id_transaction"]?>"><?php echo $forfaitsActifs["produit_nom"];?></option>
									<?php } ?>
               					</select>
								<?php } else echo $history["produit_adherent_id"];?>
               				</td>
               				<td class="col-lg-2">A déterminer</td>
               			</tr>
					<?php } ?>
               		</tbody>
               	</table>
               </section>
               <section id="resa">
               	<table class="table table-striped">
               		<thead>
               			<tr>
               				<th>Plage horaire</th>
               				<th>Lieu</th>
               				<th>Activité</th>
               				<th>Prix de la réservation</th>
               			</tr>
               		</thead>
               		<tbody>
               		<?php while($reservations = $queryResa->fetch(PDO::FETCH_ASSOC)){ ?>
               			<tr>
               				<td>Le <?php echo date_create($reservations["reservation_start"])->format('d/m/Y \d\e H\hi');?> à <?php echo date_create($reservations["reservation_end"])->format('H\hi');?></td>
               				<td><?php echo $reservations["salle_name"];?></td>
               				<td><?php echo $reservations["prestations_name"];?></td>
               				<td><?php echo $reservations["reservation_prix"];?> €</td>
               			</tr>
					<?php } ?>
               		</tbody>
               	</table>
               </section>
               <section id="forfaits">
                   <table class="table table-striped">
                       <thead>
                           <tr>
                              <th></th>
                              <th>Type de forfait</th>
                              <th>Date d'achat</th>
                              <th>Période de validité</th>
                              <th>Prix d'achat</th>
                              <th></th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php while($forfaits = $queryForfaits->fetch(PDO::FETCH_ASSOC)){
						   if($forfaits["dateActivation"] == "0000-00-00 00:00:00"){
							   $periode_validite = "Activation en attente";
						   } else {
							   $periode_validite = "Du ".date_create($forfaits["dateActivation"])->format('d/m/Y')." au ".date_create($forfaits["date_expiration"])->format('d/m/Y');
						   }
						   ?>
                           <tr>
                             <?php if($forfaits["produitActif"] == '1') { ?>
                              <td><span class="glyphicon glyphicon-certificate glyphicon-success" title="Forfait/Invitation actif(ve)"></span></td>
                              <?php } else {
							   			if($forfaits["dateActivation"] != "0000-00-00 00:00:00") { ?>
                              <td><span class="glyphicon glyphicon-certificate glyphicon-inactive" title="Forfait/Invitation inactif(ve)"></span></td>
								  <?php } else { ?>
							  <td><span class="glyphicon glyphicon-certificate glyphicon-inactive glyphicon-pending" title="Forfait/Invitation en attente"></span></td>
								  <?php } 
							   } ?>
                               <td><?php echo $forfaits["produit_nom"];?></td>
                               <td><?php echo date_create($forfaits["date_achat"])->format('d/m/Y');?></td>
                               <td><?php echo $periode_validite;?></td>
                               <td><?php echo $forfaits["prix_achat"];?> €</td>
                               <td><a href="forfait_adherent_details.php?id=<?php echo $forfaits["id_transaction"];?>&status=<?php echo $status;?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Détails...</a></td>
                           </tr>
                           <?php } ?>
                       </tbody>
                   </table>
               </section>
               <?php } if($details["est_professeur"] == 1){ ?>
               <section id="history-donnes">
                 <div class="filter-options col-lg-6">
                 	<p class="section-title">Options de filtrage</p>
                 </div>
                  <div class="price-summary col-lg-6">
                       <p class="section-title">TOTAL</p>
                       <p>Nombre de cours : <?php echo $queryHistoryDonnes->rowCount();?></p>
                       <p>Somme totale : <?php echo $totalPrice;?> €</p>
                       <p>Somme déjà réglée : <?php echo $totalPaid;?> €</p>
                       <p>Somme restante : <?php echo $totalDue = $totalPrice - $totalPaid;?> €</p>
                   </div>
                   <div id="cours-list">
                   	<table class="table table-striped">
                   	    <thead>
                   	        <tr>
                   	            <th>Intitulé <span class="glyphicon glyphicon-sort sort" data-sort="cours-name"></span></th>
                   	            <th>Jour <span class="glyphicon glyphicon-sort sort" data-sort="jour"></span></th>
                   	            <th>Niveau <span class="glyphicon glyphicon-sort sort" data-sort="niveau"></span></th>
                   	            <th>Lieu <span class="glyphicon glyphicon-sort sort" data-sort="lieu"></span></th>
                   	            <th>Somme <span class="glyphicon glyphicon-sort sort" data-sort="montant"></span></th>
                   	        </tr>
                   	    </thead>
                   	    <tbody class="list">
                   	        <?php while ($history = $queryHistoryDonnes->fetch(PDO::FETCH_ASSOC)){?>
                   	        <tr>
                   	            <td class="cours-name"><?php echo $history['cours_intitule']." ".$history['cours_suffixe'];?></td>
                   	            <td class="jour"><?php echo date_create($history['cours_start'])->format('d/m/Y H:i');?> - <?php echo date_create($history['cours_end'])->format('H:i');?></td>
                   	            <td class="niveau"><?php echo $history['niveau_name'];?></td>
                   	            <td class="lieu"><?php echo $history['salle_name'];?></td>
                   	            <td class="<?php echo ($history['paiement_effectue'] != 0)?'payment-done':'payment-due';?> montant"><?php echo $history['cours_prix'];?> €</td>
                   	        </tr>
                   	        <?php $totalPrice += $history['cours_prix'];
                   	if($history['paiement_effectue'] != 0)$totalPaid += $history['cours_prix'];} ?>
                   	    </tbody>
                   	</table>
                   </div>
               </section><!-- Historique des cours -->
               <section id="tarifs">
                   <table class="table table-striped">
                       <thead>
                           <tr>
                               <th class="col-sm-3">Intitulé</th>
                               <th class="col-sm-3">Prix</th>
                               <th class="col-sm-3">Coefficient</th>
                               <th class="col-sm-3"></th>
                           </tr>
                       </thead>
                       <tbody id="table-content">
							<tr id="new-tarif" style="display:none;">
								<td class="col-sm-3">
									<select name="prestation" id="prestation" class="form-control">
									<?php while($prestations = $queryPrestations->fetch(PDO::FETCH_ASSOC)){ ?>
										<option value="<?php echo $prestations["prestations_id"];?>"><?php echo $prestations["prestations_name"];?></option>
									<?php } ?>
									</select>
								</td>
								<td class="col-sm-3"><input type="text" name="tarif" id="tarif" class="form-control"></td>
								<td class="col-sm-3">
									<select name="ratio" id="ratio" class="form-control">
									<?php
									while ($row_ratio = $ratio->fetch(PDO::FETCH_ASSOC)){
										$array_suffixes = preg_split("/','/", substr($row_ratio['Type'], 5, strlen($row_ratio['Type'])-7));
										for($i = 0; $i < sizeof($array_suffixes); $i++){?>
										<option value="<?php echo $array_suffixes[$i];?>"><?php echo $array_suffixes[$i];?></option>
										<?php }
									} ?>
									</select>
								</td>
								<td class="col-sm-3"><button class="btn btn-default" onClick="addTarif()"><span class="glyphicon glyphicon-plus"></span> Valider</button><button class="btn btn-default" id="cancel"><span class="glyphicon glyphicon-cancel"></span> Annuler</button></td>
							</tr>
                     	<input type="hidden" name="prof_id" id="prof_id" value="<?php echo $data;?>">
                       </tbody>
                   </table>
                   <button class="btn btn-primary" id="add-tarif">AJOUTER UN TARIF</button>
                   <p id="json-output"></p>
               </section> <!-- Tarifs -->
               <section id="stats">
               	<p>Nombre de cours</p>
               	<div id="nombre-cours" style="height: 250px;"></div>
               	<p>Types de cours donnés</p>
               </section> <!-- Statistiques -->
               <?php } ?>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
   <script src="assets/js/nav-tabs.js"></script>
   <script>
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
	   
	   $("[name='link-forfait']").click(function(){
		   $("[name='forfaits-actifs']").show();
		   $("[name='link-forfait']").hide();
	   });
	   
	   $("[name='forfaits-actifs']").blur(function(){
		   var clicked = $(this);
		   var eleve_id = <?php echo $data;?>;
		   var produit_id = clicked.val();
		   var cours_id = clicked.prev().val();
		  $.post("functions/link_forfait.php", {eleve_id, cours_id, produit_id}).done(function(data){
			  showSuccessNotif(data);
			  clicked.parents("tr.warning").removeClass('warning');
			  clicked.hide();
			  clicked.parent().html(produit_id);
		  });
	   });
	   
	   $("[name='photo_identite']").fileinput({
		   previewFileType: "image",
		   showCaption: false,
		   showRemove: false,
		   showUpload: false,
		   browseClass: "btn btn-info",
		   browseLabel: "Photo",
		   browseIcon: '<i class="glyphicon glyphicon-picture"></i>'
	   });
       <?php if($details["est_professeur"] == 1){?>
       
       $("#add-tarif").click(function(){
           $("#new-tarif").show();
       });
	   
	   $("#cancel").click(function(){
		   $("#new-tarif").hide();
	   });
	   
	   $(document).ready(function(){
		   fetchTarifs();
		   
		   var options = {
			   valueNames: ['cours-name', 'jour', 'niveau', 'lieu', 'montant']
		   };
		   var coursList = new List('cours-list', options);
		   
		   var prof_id = <?php echo $data;?>;
		   $.post('functions/compile_prof_cours.php', {prof_id}).done(function(data){
			   var listeCours = JSON.parse(data);
			   
			   // Nombre de cours par jour
			   var daysArray = [["lundi",0], ["mardi",0], ["mercredi",0], ["jeudi",0], ["vendredi",0], ["samedi",0]];
			   var resDays = [];
			   for (var j = 0; j < daysArray.length; j++){
				   for (var i = 0; i < listeCours.length; i++){
					   var date = moment(listeCours[i].day).locale('fr').format('dddd');
					   if(daysArray[j][0] == date){
						   daysArray[j][1]++;
					   }
				   }
				   var graphBar = {};
				   graphBar.d = daysArray[j][0];
				   graphBar.a = daysArray[j][1];
				   resDays.push(graphBar);
			   }
			   console.log(resDays);
			   new Morris.Bar({
				   element: 'nombre-cours',
				   data : resDays,
				   xkey: 'd',
				   ykeys: ['a'],
				   labels: ['Nombre de cours']
			   });
		   })
		   
	   });
	   
	   function addTarif(){
		   var prof_id = $("#prof_id").val();
		   var prestation = $("#prestation").val();
		   var tarif = $("#tarif").val();
		   var ratio = $("#ratio").val();
		   $.post("functions/add_tarif_prof.php", {prof_id, prestation, tarif, ratio}).success(function(data){
			   $("#new-tarif").hide();
			   showSuccessNotif(data);
			   $(".fetched").remove();
			   fetchTarifs();
		   })
	   };
	   
	   function fetchTarifs(){
		   var id = $("#prof_id").val();
		   $.post("functions/get_tarifs.php", {id}).done(function(data){
			   var json = JSON.parse(data);
			   for(var i = 0; i < json.length; i++){
				   var line = "<tr class='fetched' id='tarif-"+json[i].id+"'>";
				   line += "<td class='col-sm-3 tarif-nom'>";
				   line += json[i].prestation;
				   line += "</td><td class='col-sm-3 tarif-prix'><span contenteditable='true' onblur='updateTarif("+json[i].id+")'>";
				   line += json[i].tarif;
				   line += "</span> € </td><td class='col-sm-3 tarif-ratio'>";
				   line += json[i].ratio;
				   line += "</td><td class='col-sm-3'>";
				   line += "<button class='btn btn-default' onclick='deleteTarif("+json[i].id+")'><span class='glyphicon glyphicon-trash'></span> Supprimer</button>";
				   line += "</td></tr>";
				   $("#table-content").append(line);
			   }
		   });
	   }
	   
	   function updateTarif(id){
		   var update_id = id;
		   var tarif = $("#tarif-"+update_id).children(".tarif-prix").children("span").html();
		   $.post("functions/update_tarif_prof.php", {update_id, tarif}).done(function(data){
			   showSuccessNotif(data);
			   var originalColor = $("#tarif-"+update_id).css("background-color");
			   var styles = {
				   backgroundColor : "#dff0d8",
				   transition: "0s"
			   };
			   var next = {
				   backgroundColor : originalColor,
				   transition : "2s"
			   };
			   $("#tarif-"+update_id).css(styles);
			   setTimeout(function(){ $("#tarif-"+update_id).css(next); },800);
		   });
	   }
	   
	   function deleteTarif(id){
		   var delete_id = id;
		   alert(delete_id);
		   $.post("functions/delete_tarif_prof.php", {delete_id}).done(function(data){
			   showSuccessNotif(data);
			   $(".fetched").remove();
			   fetchTarifs();
		   });
	   }
       <?php } ?>
	</script>
</body>
</html>