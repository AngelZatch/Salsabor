<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET['id'];

// On obtient les détails du professeur
$queryDetails = $db->prepare('SELECT * FROM professeurs WHERE prof_id=?');
$queryDetails->bindValue(1, $data);
$queryDetails->execute();
$details = $queryDetails->fetch(PDO::FETCH_ASSOC);

// On obtient l'historique de ses cours
$queryHistory = $db->prepare('SELECT * FROM cours JOIN niveau ON cours_niveau=niveau.niveau_id JOIN salle ON cours_salle=salle.salle_id WHERE prof_principal=? OR prof_remplacant=? ORDER BY cours_start ASC');
$queryHistory->bindValue(1, $data);
$queryHistory->bindValue(2, $data);
$queryHistory->execute();

// Tarifs
$queryTarifs = $db->prepare('SELECT * FROM tarifs_professeurs JOIN prestations ON type_prestation=prestations.prestations_id WHERE prof_id_foreign=?');
$queryTarifs->bindValue(1, $data);
$queryTarifs->execute();

// Prix de tous les cours
$totalPrice = 0;
$totalPaid = 0;
$totalDue = 0;

if(isset($_POST["edit"])){
	try{
		$db->beginTransaction();
		$edit = $db->prepare('UPDATE professeurs SET prenom = :prenom,
													nom = :nom,
													date_naissance = :date_naissance,
													rue = :rue,
													code_postal = :code_postal,
													ville = :ville,
													mail = :mail,
													tel_fixe = :tel_fixe,
													tel_port = :tel_port
													WHERE prof_id = :id');
		$edit->bindValue(':prenom', $_POST["prenom"]);
		$edit->bindValue(':nom', $_POST["nom"]);
		$edit->bindValue(':date_naissance', date_create($_POST["date_naissance"])->format('Y-m-d'));
		$edit->bindParam(':rue', $_POST["rue"]);
		$edit->bindParam(':code_postal', $_POST["code_postal"]);
		$edit->bindParam(':ville', $_POST["ville"]);
		$edit->bindParam(':mail', $_POST["mail"]);
		$edit->bindParam(':tel_fixe', $_POST["tel_fixe"]);
		$edit->bindParam(':tel_port', $_POST["tel_port"]);
		$edit->bindParam(':id', $data);
		$edit->execute();
		$db->commit();
		header("Location:profs_details.php?id=$data");
	} catch (PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
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
              <div class="btn-toolbar" id="top-page-buttons">
                   <a href="profs_liste.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à la liste des professeurs</a>
                </div> <!-- btn-toolbar -->
               <h1 class="page-title"><span class="glyphicon glyphicon-user"></span>
                   <?php echo $details['prenom']." ".$details['nom'];?>
               </h1>
               <ul class="nav nav-tabs">
                   <li role="presentation" id="infos-toggle"><a>Informations personnelles</a></li>
                   <li role="presentation" id="history-toggle" class="active"><a>Historique des cours</a></li>
                   <li role="presentation" id="tarifs-toggle"><a>Tarifs</a></li>
               </ul>
               <section id="infos">
                   <form method="post">
                       <div class="form-group">
                          <label for="prenom" class="control-label">Prénom</label>
                           <input type="text" name="prenom" class="form-control" value="<?php echo $details['prenom'];?>">
                       </div>
                       <div class="form-group">
                          <label for="" form="nom" class="control-label">Nom</label>
                           <input type="text" name="nom" class="form-control" value="<?php echo $details['nom'];?>">
                       </div>
                       <div class="form-group">
                          <label for="mail" class="control-label">Adresse mail</label>
                           <input type="text" name="mail" class="form-control" value="<?php echo $details['mail'];?>">
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
                          <label for="tel_fixe" class="control-label">Téléphone fixe</label>
                           <input type="text" name="tel_fixe" class="form-control" value="<?php echo $details['tel_fixe'];?>">
                       </div>
                       <div class="form-group">
                          <label for="tel_port" class="control-label">Téléphone portable</label>
                           <input type="text" name="tel_port" class="form-control" value="<?php echo $details['tel_port'];?>">
                       </div>
						<div class="form-group">
							<label for="date_naissance" class="control-label">Date de naissance</label>
							<input type="date" name="date_naissance" id="date_naissance" class="form-control" value=<?php echo $details["date_naissance"];?>>
						</div>
					  <input type="submit" name="edit" role="button" class="btn btn-primary" value="ENREGISTRER LES MODIFICATIONS" style="width:100%;">
                   </form>
               </section> <!-- Informations personnelles -->
               <section id="history">
                   <table class="table table-striped">
                       <thead>
                           <tr>
                               <th>Intitulé</th>
                               <th>Jour</th>
                               <th>Niveau</th>
                               <th>Lieu</th>
                               <th>Somme</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php while ($history = $queryHistory->fetch(PDO::FETCH_ASSOC)){?>
                           <tr>
                               <td><?php echo $history['cours_intitule']." ".$history['cours_suffixe'];?></td>
                               <td><?php echo date_create($history['cours_start'])->format('d/m/Y H:i');?> - <?php echo date_create($history['cours_end'])->format('H:i');?></td>
                               <td><?php echo $history['niveau_name'];?></td>
                               <td><?php echo $history['salle_name'];?></td>
                               <td class="<?php echo ($history['paiement_effectue'] != 0)?'payment-done':'payment-due';?>"><?php echo $history['cours_prix'];?> €</td>
                           </tr>
                           <?php $totalPrice += $history['cours_prix'];
if($history['paiement_effectue'] != 0)$totalPaid += $history['cours_prix'];} ?>
                       </tbody>
                   </table>
                    <div class="price-summary">
                       <p>TOTAL</p>
                       <p>Nombre de cours : <?php echo $queryHistory->rowCount();?></p>
                       <p>Somme totale : <?php echo $totalPrice;?> €</p>
                       <p>Somme déjà réglée : <?php echo $totalPaid;?> €</p>
                       <p>Somme restante : <?php echo $totalDue = $totalPrice - $totalPaid;?> €</p>
                   </div>
               </section><!-- Historique des cours -->
               <section id="tarifs">
                   <table class="table table-striped">
                       <thead>
                           <tr>
                               <th>Intitulé</th>
                               <th>Prix</th>
                               <th>Coefficient</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php while($tarifs = $queryTarifs->fetch(PDO::FETCH_ASSOC)){?>
                           <tr>
                               <td><?php echo $tarifs['prestations_name'];?></td>
                               <td><?php echo $tarifs['tarif_prestation'];?> €</td>
                               <td><?php echo $tarifs['ratio_multiplicatif'];?></td>
                           </tr>
                           <?php } ?>
                       </tbody>
                   </table>
                   <!--<button class="btn btn-primary">AJOUTER UN TARIF</button>-->
               </section> <!-- Tarifs -->
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script src="assets/js/nav-tabs.js"></script>  
</body>
</html>