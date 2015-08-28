<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$status = $_GET["status"];
$valueTeacher = 0;
$valueEleve = 0;
$valueStaff = 0;
$backLink = "";
$titleText = "Inscrire un ";
switch($status){
	case "teacher":
	$valueTeacher = 1;
	$backLink = "professeurs.php";
	$buttonText = "Retour aux professeurs";
	$titleText .= "professeur";
	break;

	case "eleve":
	$valueEleve = 1;
	$backLink = "adherents.php";
	$buttonText = "Retour aux élèves";
	$titleText .= "élève";
	break;

	case "contact":
	$backLink = "dashboard.php";
	$buttonText = "Retour au panneau d'administration";
	$titleText .= "contact";
	break;

	case "staff":
	$valueStaff = 1;
	$backLink = "staff_liste.php?rank=0";
	$buttonText = "Retour aux membres du staff";
	$titleText .= "staff";
	break;
}


$now = date_create('now')->format('Y-m-d');

$connaissances = $db->query("SELECT * FROM sources_connaissance");

if(isset($_POST['addAdherent'])){
	// Upload de l'image
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
	print_r($_FILES);

	// Champs par défaut
	$actif = 1;
	$acces_web = 1;
	try{
		$db->beginTransaction();
		$new = $db->prepare('INSERT INTO users(user_prenom, user_nom, user_rfid, date_naissance,
												date_inscription, rue, code_postal, ville, mail,
												telephone, tel_secondaire, photo, source_connaissance,
												acces_web, est_membre, est_professeur, est_staff, est_prestataire,
												est_autre, autre_statut, user_rib, actif)
										VALUES(:prenom, :nom, :rfid, :date_naissance,
												:date_inscription, :rue, :code_postal, :ville, :mail,
												:telephone, :tel_secondaire, :photo, :sources_connaissance,
												:acces_web, :est_membre, :est_professeur, :est_staff, :est_prestataire,
												:est_autre, :autre_statut, :user_rib, :actif)');
		$new->bindParam(':prenom', $_POST['identite_prenom']);
		$new->bindParam(':nom', $_POST['identite_nom']);
		$new->bindParam(':rfid', $_POST["rfid"]);
		$new->bindParam(':date_naissance', $_POST['date_naissance']);
		$new->bindParam(':date_inscription', $_POST['date_inscription']);
		$new->bindParam(':rue', $_POST['rue']);
		$new->bindParam(':code_postal', $_POST['code_postal']);
		$new->bindParam(':ville', $_POST['ville']);
		$new->bindParam(':mail', $_POST['mail']);
		$new->bindParam(':telephone', $_POST['telephone']);
		$new->bindParam(':tel_secondaire', $_POST["tel_secondaire"]);
		$new->bindParam(':photo', $target_file);
		$new->bindParam(':sources_connaissance', $_POST["sources_connaissance"]);
		$new->bindParam(':acces_web', $acces_web);
		$new->bindParam(':est_membre', $_POST["est_membre"]);
		$new->bindParam(':est_professeur', $_POST["est_professeur"]);
		$new->bindParam(':est_staff', $_POST["est_staff"]);
		$new->bindParam(':est_prestataire', $_POST["est_prestataire"]);
		$new->bindParam(':est_autre', $_POST["est_autre"]);
		$new->bindParam(':autre_statut', $_POST["est_statut"]);
		$new->bindParam(':user_rib', $_POST["user_rib"]);
		$new->bindParam(':actif', $actif);
		$new->execute();
		if(isset($_POST["rfid"])){
			$delete = $db->prepare('DELETE FROM passages WHERE passage_eleve=? AND status=1');
			$delete->bindParam(1, $_POST["rfid"]);
			$delete->execute();
		}
		$db->commit();
		echo "Succès lors de l'ajout";
		header('Location: adherents.php');
	} catch(PDOException $e){
		$db->rollBack();
		echo $e->getMessage();
	}
}
?>
<html>
<head>
	<meta charset="UTF-8">
	<title>Inscription d'un adhérent | Salsabor</title>
	<?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
	   <div class="row">
		   <?php include "side-menu.php";?>
		   <div class="col-sm-10 main">
			  <p id="current-time"></p>
			   <h1 class="page-title"><span class="glyphicon glyphicon-pencil"></span> <?php echo $titleText;?></h1>
				<form action="inscription.php" method="post" role="form" id="add_adherent" enctype="multipart/form-data">
				  <div class="btn-toolbar">
					   <a href="<?php echo $backLink;?>" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> <?php echo $buttonText;?></a>
					   <input type="submit" name="addAdherent" role="button" class="btn btn-primary" value="ENREGISTRER" id="submit-button" disabled>
				</div> <!-- btn-toolbar -->
				<p class="form-section">Informations personnelles</p>
				<div class="form-group">
					<label for="identite_prenom" class="control-label">Prénom</label>
					<input type="text" name="identite_prenom" id="identite_prenom" class="form-control mandatory" placeholder="Prénom">
				</div>
				<div class="form-group">
					<label for="identite_nom" class="control-label">Nom</label>
					<input type="text" name="identite_nom" id="identite_nom" class="form-control mandatory" placeholder="Nom">
				</div>
				<div class="form-group">
					<label for="profile_picture" class="control-label">Photo d'identité</label>
					<input type="file" class="form-control" name="photo_identite">
				</div>
				<div class="form-group">
					<label for="certificat_medical" class="control-label">Certificat Médical</label>
					<input type="file" class="form-control" name="certificat_medical">
				</div>
				<div class="form-group">
				<label for="" class="control-label">Adresse postale</label>
					<input type="text" name="rue" id="rue" placeholder="Adresse" class="form-control mandatory">
				</div>
				<div class="form-group">
					<input type="text" name="code_postal" id="code_postal" placeholder="Code Postal" class="form-control mandatory">
				</div>
				<div class="form-group">
					<input type="text" name="ville" id="ville" placeholder="Ville" class="form-control mandatory">
				</div>
				<div class="form-group">
				<label for="mail" class="control-label">Adresse mail</label>
					<input type="mail" name="mail" id="mail" placeholder="Adresse mail" class="form-control mandatory">
				</div>
				<div class="form-group">
				<label for="telephone" class="control-label">Téléphone principal</label>
					<input type="text" name="telephone" id="telephone" placeholder="Numéro de téléphone principal" class="form-control mandatory">
				</div>
				<div class="form-group">
				<label for="tel_secondaire" class="control-label">Téléphone secondaire</label>
				<input type="text" name="tel_secondaire" class="form-control" placeholder="Numéro de téléphone secondaire">
				</div>
				<div class="form-group">
					<label for="date_naissance" class="control-label">Date de naissance</label>
					<input type="date" name="date_naissance" id="date_naissance" class="form-control mandatory">
				</div>
				<p class="form-section">Informations Salsabor</p>
				<div class="form-group">
					<label for="date_inscription" class="control-label">Date d'inscription <span class="label-tip">Par défaut, aujourd'hui</span></label>
					<input type="date" name="date_inscription" id="date_inscription" class="form-control mandatory" value="<?php echo $now;?>">
				</div>
				<div class="form-group">
					<label for="parrain" class="control-label">Parrain</label>
					<input type="text" name="parrain" class="form-control" placeholder="Tapez un nom pour rechercher">
				</div>
				<div class="form-group">
					<label for="rfid" class="control-label">Code carte</label>
					<div class="input-group">
						<input type="text" name="rfid" class="form-control" placeholder="Scannez une nouvelle puce pour récupérer le code RFID">
						<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" name="fetch-rfid">Lancer la détection</a></span>
					</div>
				</div>
				<div class="form-group">
					<label for="statuts" class="control-label">Statut du contact <span class="label-tip">Cochez autant que nécessaire</span></label><br>
					<label for="est_membre" class="control-label">Membre</label>
					<input name="est_membre" id="est_membre" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $valueEleve;?>">
					<label for="est_professeur" class="control-label">Professeur</label>
					<input name="est_professeur" id="est_professeur" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $valueTeacher;?>">
					<label for="est_staff" class="control-label">Staff</label>
					<input name="est_staff" id="est_staff" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $valueStaff;?>">
					<label for="est_prestataire" class="control-label">Prestataire</label>
					<input name="est_prestataire" id="est_prestataire" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
					<label for="est_autre" class="contorl-label">Autre <span class="label-tip">Spécifiez ci-dessous</span></label>
					<input type="text" name="est_autre" id="est_autre" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
				</div>
				<div class="form-group" id="autre" style="display:none;">
					<label for="autre_statut" class="control-label">Autre statut</label>
					<input type="text" name="autre_statut" class="form-control">
				</div>
				<div class="form-group" id="rib-data" style="display:none;">
					<label for="rib" class="control-label">Informations bancaires <span class="label-tip">Pour un professeur, un staff ou un prestataire</span></label>
					<input type="text" name="rib" class="form-control">
				</div>
				<div class="form-group">
					<label for="sources_connaissance" class="control-label">Par où avez vous connu Salsabor ? <span class="label-tip">Sélectionnez la source la plus influente</span></label>
					<select name="sources_connaissance" class="form-control mandatory">
						<?php while($sources = $connaissances->fetch(PDO::FETCH_ASSOC)){ ?>
							<option value="<?php echo $sources["source_id"];?>"><?php echo $sources["source"];?></option>
						<?php } ?>
					</select>
				</div>
			  </form>
		   </div>
	   </div>
   </div>
   <?php include "scripts.php";?>
	  <script>
		  $(document).ready(function(){
			  $("#est_autre").change(function(){
				  if($(this).val() == '1'){
					  $("#autre").show('600');
				  } else {
					  $("#autre").hide('600');
				  }
			  });

			  $(".rib-toggle").change(function(){
				  if($("#est_staff").val() == '0' && $("#est_professeur").val() == '0' && $("#est_prestataire").val() == '0'){
					  $("#rib-data").hide('600');
				  } else {
					  $("#rib-data").show('600');
				  }
			  })

		  });
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
		  $("[name='photo_identite']").fileinput({
			  previewFileType: "image",
			  showUpload: false,
			  showCaption: false,
			  showRemove: false,
			  browseClass: "btn btn-info btn-block",
			  browseLabel: " Sélectionnez une image",
			  browseIcon: '<i class="glyphicon glyphicon-picture"></i>'
		  });

		  $("[name='certificat_medical']").fileinput({
			  previewFileType: "image",
			  showUpload: false,
			  showCaption: false,
			  showRemove: false,
			  browseClass: "btn btn-info btn-block",
			  browseLabel: " Sélectionnez un fichier",
			  browseIcon: '<i class="glyphicon glyphicon-open-file"></i>'
		  });
	</script>
</body>
</html>
