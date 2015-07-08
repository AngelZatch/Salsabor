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
				<div class="btn-toolbar" id="top-page-buttons">
                   <a href="adherents.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à la liste des adhérents</a>
                </div> <!-- btn-toolbar -->
               <h1 class="page-title"><span class="glyphicon glyphicon-user"></span> <?php echo $details["eleve_prenom"]." ".$details["eleve_nom"];?></h1>
			  <ul class="nav nav-tabs">
                   <li role="presentation" id="infos-toggle" class="active"><a>Informations personnelles</a></li>
                   <li role="presentation" id="history-toggle"><a>Historique des cours</a></li>
               </ul>
               <section id="infos">
               		<form action="">
						<div class="container-fluid">
               				<div class="form-group col-sm-2 thumbnail" id="picture-container">
								<img src="<?php echo $details["photo"];?>" alt="Pas de photo">
								<input type="file" class="form-control" name="photo_identite" style="display:none;">
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
					  <input type="submit" name="addAdherent" role="button" class="btn btn-primary" value="ENREGISTRER" style="width:100%;">
					  </form>
               </section>
               <section id="history">
               	<table class="table table-striped">
               		<thead>
               			<tr>
               				<th>Intitulé</th>
               				<th>Jour</th>
               				<th>Niveau</th>
               				<th>Lieu</th>
               				<th>Prix pondéré</th>
               			</tr>
               		</thead>
               		<tbody>
               		<?php while($history = $queryHistory->fetch(PDO::FETCH_ASSOC)){ ?>
               			<tr>
               				<td><?php echo $history['cours_intitule']." ".$history['cours_suffixe'];?></td>
               				<td><?php echo date_create($history['cours_start'])->format('d/m/Y H:i');?> - <?php echo date_create($history['cours_end'])->format('H:i');?></td>
               				<td><?php echo $history['niveau_name'];?></td>
               				<td><?php echo $history['salle_name'];?></td>
               				<td>A déterminer</td>
               			</tr>
               		</tbody>
					<?php } ?>
               	</table>
               </section>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
   <script src="assets/js/nav-tabs.js"></script>
</body>
</html>