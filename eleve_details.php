<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// On obtient les détails de l'adhérent
$queryDetails = $db->prepare('SELECT * FROM adherents WHERE eleve_id=?');
$queryDetails->bindValue(1, $data);
$queryDetails->execute();
$details = $queryDetails->fetch(PDO::FETCH_ASSOC);

// On obtient l'historique de ses cours
$queryHistory = $db->prepare('SELECT * FROM cours_participants JOIN cours ON cours_id_foreign=cours.cours_id JOIN niveau ON cours.cours_niveau=niveau.niveau_id JOIN salle ON cours.cours_salle=salle.salle_id WHERE eleve_id_foreign=?');
$queryHistory->bindValue(1, $data);
$queryHistory->execute();

$queryResa = $db->prepare('SELECT * FROM reservations JOIN adherents ON reservation_personne=adherents.eleve_id JOIN prestations ON type_prestation=prestations_id JOIN salle ON reservation_salle=salle.salle_id WHERE reservation_personne=?');
$queryResa->bindValue(1, $data);
$queryResa->execute();

$queryForfaits = $db->prepare('SELECT *, produits_adherents.date_activation AS dateActivation FROM produits_adherents JOIN adherents ON id_adherent=adherents.eleve_id JOIN produits ON id_produit=produits.produit_id WHERE id_adherent=?');
$queryForfaits->bindValue(1, $data);
$queryForfaits->execute();

$queryForfaitsActifs = $db->prepare("SELECT * FROM produits_adherents JOIN produits ON id_produit=produits.produit_id WHERE id_adherent=? AND produits_adherents.actif=1");
$queryForfaitsActifs->bindParam(1, $data);
$queryForfaitsActifs->execute();

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
		$edit = $db->prepare('UPDATE adherents SET eleve_prenom = :prenom,
													eleve_nom = :nom,
													numero_rfid = :rfid,
													date_naissance = :date_naissance,
													rue = :rue,
													code_postal = :code_postal,
													ville = :ville,
													mail = :mail,
													telephone = :telephone,
													photo = :photo
													WHERE eleve_id = :id');
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
		header("Location:eleve_details.php?id=$data");
	} catch(PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editer - <?php echo $details["eleve_prenom"]." ".$details["eleve_nom"];?> | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
				<div class="btn-toolbar" id="top-page-buttons">
                   <a href="adherents.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à la liste des adhérents</a>
                </div> <!-- btn-toolbar -->
               <h1 class="page-title"><span class="glyphicon glyphicon-user"></span> <?php echo $details["eleve_prenom"]." ".$details["eleve_nom"];?></h1>
			  <ul class="nav nav-tabs">
                   <li role="presentation" id="infos-toggle" class="active"><a>Informations personnelles</a></li>
                   <li role="presentation" id="history-toggle"><a>Historique des cours</a></li>
                   <li role="presentation" id="resa-toggle"><a>Historique des réservations</a></li>
                   <li role="presentation" id="forfaits-toggle"><a>Historique des forfaits</a></li>
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
								<input type="text" name="identite_prenom" id="identite_prenom" class="form-control" placeholder="Prénom" value="<?php echo $details["eleve_prenom"];?>">
							</div>
							<div class="form-group col-sm-10">
							<label for="identite_nom" class="control-label">Nom</label>
								<input type="text" name="identite_nom" id="identite_nom" class="form-control" placeholder="Nom" value="<?php echo $details["eleve_nom"];?>">
							</div>
							<div class="form-group col-sm-10">
								<label for="mail" class="control-label">Adresse mail</label>
								<input type="text" name="mail" id="mail" placeholder="Adresse mail" class="form-control" value="<?php echo $details["mail"];?>">
							</div>
               			</div>
               			<div class="form-group">
               				<label for="rfid" class="control-label">Code carte</label>
               				<div class="input-group">
               					<input type="text" name="rfid" class="form-control" placeholder="Scannez une nouvelle puce pour récupérer le code RFID" value="<?php echo $details["numero_rfid"];?>">
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
					  <input type="submit" name="edit" role="button" class="btn btn-primary" value="ENREGISTRER LES MODIFICATIONS" style="width:100%;">
					  </form>
               </section>
               <section id="history">
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
               		<?php while($history = $queryHistory->fetch(PDO::FETCH_ASSOC)){ ?>
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
                               <th>Type de forfait</th>
                               <th>Date d'achat</th>
                               <th>Période de validité</th>
                               <th>Prix d'achat</th>
                               <th></th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php while($forfaits = $queryForfaits->fetch(PDO::FETCH_ASSOC)){ ?>
                           <tr>
                               <td><?php echo $forfaits["produit_nom"];?></td>
                               <td><?php echo date_create($forfaits["date_achat"])->format('d/m/Y');?></td>
                               <td>Du <?php echo date_create($forfaits["dateActivation"])->format('d/m/Y');?> au <?php echo date_create($forfaits["date_expiration"])->format('d/m/Y');?></td>
                               <td><?php echo $forfaits["prix_achat"];?> €</td>
                               <td><a href="forfait_adherent_details.php?id=<?php echo $forfaits["id_transaction"];?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Détails...</a></td>
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
	</script>
</body>
</html>