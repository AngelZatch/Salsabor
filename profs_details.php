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

// Prestations
$queryPrestations = $db->query('SELECT * FROM prestations WHERE est_cours=1');

// Types de ratio multiplicatif
$ratio = $db->query("SHOW COLUMNS FROM tarifs_professeurs LIKE 'ratio_multiplicatif'");

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
    <title>Détails du professeur <?php echo $details['prenom']." ".$details['nom'];?> | Salsabor</title>
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
				<div class="alert alert-success" id="tarif-added" style="display:none;">Tarif ajouté avec succès</div>
				<div class="alert alert-success" id="tarif-updated" style="display:none;">Tarif modifié avec succès</div>
				<div class="alert alert-success" id="tarif-deleted" style="display:none;">Tarif supprimé avec succès</div>
				<div class="class alert alert-danger" id="tarif-error" style="display:none;">Erreur. Certains champs sont vides</div>
               <h1 class="page-title"><span class="glyphicon glyphicon-user"></span>
                   <?php echo $details['prenom']." ".$details['nom'];?>
               </h1>
               <ul class="nav nav-tabs">
                   <li role="presentation" id="infos-toggle"><a>Informations personnelles</a></li>
                   <li role="presentation" id="history-toggle"><a>Historique des cours</a></li>
                   <li role="presentation" id="tarifs-toggle" class="active"><a>Tarifs</a></li>
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
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script src="assets/js/nav-tabs.js"></script>  
   <script>
		$("#add-tarif").click(function(){
			$("#new-tarif").show();
		});
	   
	   $("#cancel").click(function(){
		   $("#new-tarif").hide();
	   });
	   
	   $(document).ready(function(){
		   fetchTarifs();
	   });
	   
	   function addTarif(){
		   var prof_id = $("#prof_id").val();
		   var prestation = $("#prestation").val();
		   var tarif = $("#tarif").val();
		   var ratio = $("#ratio").val();
		   $.post("functions/add_tarif_prof.php", {prof_id, prestation, tarif, ratio}).success(function(data){
			   $("#new-tarif").hide();
			   $('#tarif-added').show('500').delay(3000).hide('3000');
			   $(".fetched").remove();
			   fetchTarifs();
		   }).fail(function(data){
			   $('#tarif-error').show('500').delay(3000).hide('3000');
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
			   $('#tarif-updated').show('500').delay(3000).hide('3000');
			   var originalColor = $("#tarif-"+update_id).css("background-color");
			   var styles = {
				   backgroundColor : "#dff0d8"
			   };
			   var next = {
				   backgroundColor : originalColor,
				   transition : "2s"
			   };
			   $("#tarif-"+update_id).css(styles);
			   setTimeout(function(){ $("#tarif-"+update_id).css(next); },800);
		   }).fail(function(data){
			   $('#tarif-error').show('500').delay(3000).hide('3000');
		   });
	   }
	   
	   function deleteTarif(id){
		   var delete_id = id;
		   alert(delete_id);
		   $.post("functions/delete_tarif_prof.php", {delete_id}).done(function(data){
			   $('#tarif-deleted').show('500').delay(3000).hide('3000');
			   $(".fetched").remove();
			   fetchTarifs();
		   }).fail(function(data){
			   $('#tarif-error').show('500').delay(3000).hide('3000');
		   })
	   }
	</script>
</body>
</html>