<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

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
	try{
		$db->beginTransaction();
		$new = $db->prepare('INSERT INTO adherents(eleve_prenom, eleve_nom, numero_rfid, date_naissance, date_inscription, rue, code_postal, ville, mail, telephone, photo)
		VALUES(:prenom, :nom, :rfid, :date_naissance, :date_inscription, :rue, :code_postal, :ville, :mail, :telephone, :photo)');
		$new->bindParam(':prenom', $_POST['identite_prenom']);
		$new->bindParam(':nom', $_POST['identite_nom']);
		$new->bindParam(':rfid', $_POST["rfid"]);
		$new->bindParam(':date_naissance', $_POST['date_naissance']);
		$new->bindParam(':date_inscription', date_create('now')->format('Y-m-d'));
		$new->bindParam(':rue', $_POST['rue']);
		$new->bindParam(':code_postal', $_POST['code_postal']);
		$new->bindParam(':ville', $_POST['ville']);
		$new->bindParam(':mail', $_POST['mail']);
		$new->bindParam(':telephone', $_POST['telephone']);
		$new->bindParam(':photo', $target_file);
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
               <h1 class="page-title"><span class="glyphicon glyphicon-pencil"></span> Inscrire un adhérent</h1>
				<div class="col-sm-9" id="solo-form">
					<form action="eleve_add.php" method="post" class="form-horizontal" role="form" id="add_adherent" enctype="multipart/form-data">
					  <div class="btn-toolbar">
					 	  <a href="adherents.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour aux adhérents</a>
					 	  <input type="submit" name="addAdherent" role="button" class="btn btn-primary" value="ENREGISTRER">
					</div> <!-- btn-toolbar -->
					<div class="form-group">
						<label for="identite_prenom" class="control-label">Prénom</label>
						<input type="text" name="identite_prenom" id="identite_prenom" class="form-control" placeholder="Prénom">
					</div>
					<div class="form-group">
					<label for="identite_nom" class="control-label">Nom</label>
						<input type="text" name="identite_nom" id="identite_nom" class="form-control" placeholder="Nom">
					</div>
					<div class="form-group">
						<label for="profile_picture" class="control-label">Photo d'identité</label>
						<input type="file" class="form-control" name="photo_identite">
					</div>
					<div class="form-group">
						<label for="certificat_medical" class="control-label">Certificat Médical</label>
						<input type="file" class="form-control">
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
					<label for="mail" class="control-label">Adresse mail</label>
						<input type="text" name="mail" id="mail" placeholder="Adresse mail" class="form-control">
					</div>
					<div class="form-group">
					<label for="telephone" class="control-label">Téléphone</label>
						<input type="text" name="telephone" id="telephone" placeholder="Numéro de téléphone" class="form-control">
					</div>
					<div class="form-group">
						<label for="date_naissance" class="control-label">Date de naissance</label>
						<input type="date" name="date_naissance" id="date_naissance" class="form-control">
					</div>
					<div class="form-group">
						<label for="rfid" class="control-label">Code carte</label>
						<div class="input-group">
							<input type="text" name="rfid" class="form-control" placeholder="Scannez une nouvelle puce pour récupérer le code RFID">
							<span role="buttton" class="input-group-btn"><a class="btn btn-primary" role="button" name="fetch-rfid">Lancer la détection</a></span>
						</div>
					</div>
				  <input type="submit" name="addAdherent" role="button" class="btn btn-primary" value="ENREGISTRER" style="width:100%;">
				  </form>
				</div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
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
	</script>    
</body>
</html>