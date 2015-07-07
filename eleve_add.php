<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

if(isset($_POST['addAdherent'])){
	// Upload de l'image
	$target_dir = "assets/pictures/";
	$target_file = $target_dir.basename($_FILES['fileToUpload']['name']);
	$uploadOk = 1;
	$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
	$check = getimagesize($_FILES['fileToUpload']['tmp_name']);
	if(!$check){
		$uploadOk = 1;
	} else {
		echo "Fichier non conforme";
		$uploadOk = 0;
	}
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Template - Salsabor</title>
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
					<form action="adherents.php" method="post" class="form-horizontal" role="form" id="add_adherent" enctype="multipart/form-data">
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
				  <input type="submit" name="addAdherent" role="button" class="btn btn-primary" value="ENREGISTRER" style="width:100%;">
				  </form>
				</div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>