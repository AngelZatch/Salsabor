<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// User details
$details = $db->query("SELECT * FROM users u
						WHERE user_id='$data'")->fetch(PDO::FETCH_ASSOC);

$details["count"] = $db->query("SELECT * FROM tasks
					WHERE ((task_token LIKE '%USR%' AND task_target = '$data')
					OR (task_token LIKE '%PRD%' AND task_target = (SELECT id_produit_adherent FROM produits_adherents WHERE id_user_foreign = '$data'))
					OR (task_token LIKE '%TRA%' AND task_target = (SELECT id_transaction FROM transactions WHERE payeur_transaction = '$data')))
						AND task_state = 0")->rowCount();

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

// Et on cherche à savoir si des échéances sont en retard
$queryEcheances = $db->query("SELECT * FROM produits_echeances JOIN transactions ON reference_achat=transactions.id_transaction WHERE echeance_effectuee=2 AND payeur_transaction=$data")->rowCount();

// Edit des informations
if(isset($_POST["edit"])){
	if($_FILES["photo"]["name"]){
		$target_dir = "assets/pictures/";
		$target_file = $target_dir.basename($_FILES["photo"]["name"]);
		$picture = $target_dir.$data.".".pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
		move_uploaded_file($_FILES["photo"]["tmp_name"], $picture);
		try{
			$db->beginTransaction();
			$edit = $db->prepare('UPDATE users
								SET user_prenom = :prenom, user_nom = :nom, user_rfid = :rfid,
									date_naissance = :date_naissance, rue = :rue, code_postal = :code_postal, ville = :ville,
									mail = :mail, telephone = :telephone, tel_secondaire = :tel_secondaire, photo = :photo,
									est_membre = :est_membre, est_professeur = :est_professeur, est_staff = :est_staff, est_prestataire = :est_prestataire, est_autre = :est_autre, commentaires = :commentaires
													WHERE user_id = :id');
			$edit->bindParam(':prenom', $_POST["user_prenom"]);
			$edit->bindParam(':nom', $_POST["user_nom"]);
			$edit->bindParam(':rfid', $_POST["rfid"]);
			$edit->bindParam(':date_naissance', $_POST["date_naissance"]);
			$edit->bindParam(':rue', $_POST["rue"]);
			$edit->bindParam(':code_postal', $_POST["code_postal"]);
			$edit->bindParam(':ville', $_POST["ville"]);
			$edit->bindParam(':mail', $_POST["mail"]);
			$edit->bindParam(':telephone', $_POST["telephone"]);
			$edit->bindParam(':tel_secondaire', $_POST["tel_secondaire"]);
			$edit->bindParam(':photo', $picture);
			$edit->bindParam(':est_membre', $_POST["est_membre"]);
			$edit->bindParam(':est_professeur', $_POST["est_professeur"]);
			$edit->bindParam(':est_staff', $_POST["est_staff"]);
			$edit->bindParam(':est_prestataire', $_POST["est_prestataire"]);
			$edit->bindParam(':est_autre', $_POST["est_autre"]);
			$edit->bindParam(':commentaires', $_POST["commentaires"]);
			$edit->bindParam(':id', $data);
			$edit->execute();
			if(isset($_POST["rfid"])){
				$delete = $db->prepare('DELETE FROM participations WHERE user_rfid = ? AND status=1');
				$delete->bindParam(1, $_POST["rfid"]);
				$delete->execute();
			}
			$db->commit();
			header("Location:$data");
		} catch(PDOException $e){
			$db->rollBack();
			var_dump($e->getMessage());
		}
	} else {
		try{
			$db->beginTransaction();
			$edit = $db->prepare('UPDATE users
								SET user_prenom = :prenom, user_nom = :nom, user_rfid = :rfid,
									date_naissance = :date_naissance, rue = :rue, code_postal = :code_postal, ville = :ville,
									mail = :mail, telephone = :telephone, tel_secondaire = :tel_secondaire,
									est_membre = :est_membre, est_professeur = :est_professeur, est_staff = :est_staff, est_prestataire = :est_prestataire, est_autre = :est_autre, commentaires = :commentaires
													WHERE user_id = :id');
			$edit->bindParam(':prenom', $_POST["user_prenom"]);
			$edit->bindParam(':nom', $_POST["user_nom"]);
			$edit->bindParam(':rfid', $_POST["rfid"]);
			$edit->bindParam(':date_naissance', $_POST["date_naissance"]);
			$edit->bindParam(':rue', $_POST["rue"]);
			$edit->bindParam(':code_postal', $_POST["code_postal"]);
			$edit->bindParam(':ville', $_POST["ville"]);
			$edit->bindParam(':mail', $_POST["mail"]);
			$edit->bindParam(':telephone', $_POST["telephone"]);
			$edit->bindParam(':tel_secondaire', $_POST["tel_secondaire"]);
			$edit->bindParam(':est_membre', $_POST["est_membre"]);
			$edit->bindParam(':est_professeur', $_POST["est_professeur"]);
			$edit->bindParam(':est_staff', $_POST["est_staff"]);
			$edit->bindParam(':est_prestataire', $_POST["est_prestataire"]);
			$edit->bindParam(':est_autre', $_POST["est_autre"]);
			$edit->bindParam(':commentaires', $_POST["commentaires"]);
			$edit->bindParam(':id', $data);
			$edit->execute();
			if(isset($_POST["rfid"])){
				$delete = $db->prepare('DELETE FROM participations WHERE user_rfid = ? AND status=1');
				$delete->bindParam(1, $_POST["rfid"]);
				$delete->execute();
			}
			$db->commit();
			header("Location:$data");
		} catch(PDOException $e){
			$db->rollBack();
			var_dump($e->getMessage());
		}
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Editer - <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<legend>Informations</legend>
					<?php if($queryEcheances != 0){ ?>
					<div class="alert alert-danger"><strong>Attention !</strong> Cet adhérent a des échéances en retard.</div>
					<?php } ?>
					<ul class="nav nav-tabs">
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/historique">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/taches">Tâches</a></li>
						<?php if($details["est_professeur"] == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<form method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
						<div class="form-group">
							<label for="user_prenom" class="col-lg-3 control-label">Prénom</label>
							<div class="col-sm-9">
								<input type="text" name="user_prenom" id="user_prenom" class="form-control" placeholder="Prénom" value="<?php echo $details["user_prenom"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="user_nom" class="col-lg-3 control-label">Nom</label>
							<div class="col-sm-9">
								<input type="text" name="user_nom" id="user_nom" class="form-control" placeholder="Nom de famille" value="<?php echo $details["user_nom"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="mail" class="col-lg-3 control-label">Adresse mail</label>
							<div class="col-lg-9">
								<input type="email" name="mail" id="mail" placeholder="Adresse mail" class="form-control" value="<?php echo $details["mail"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="statuts" class="col-lg-3 control-label">Statut(s) du contact</label>
							<div class="col-lg-9">
								<label for="est_membre" class="control-label">Membre</label>
								<input name="est_membre" id="est_membre" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $details["est_membre"];?>">
								<label for="est_professeur" class="control-label">Professeur</label>
								<input name="est_professeur" id="est_professeur" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $details["est_professeur"];?>">
								<label for="est_staff" class="control-label">Staff</label>
								<input name="est_staff" id="est_staff" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $details["est_staff"];?>">
								<label for="est_prestataire" class="control-label">Prestataire</label>
								<input name="est_prestataire" id="est_prestataire" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $details["est_prestataire"];?>">
								<label for="est_autre" class="contorl-label">Autre <span class="label-tip">Spécifiez en commentaire</span></label>
								<input name="est_autre" id="est_autre" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $details["est_autre"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="avatar" class="col-lg-3 control-label">Photo de profil</label>
							<div class="col-lg-9">
								<div id="kv-avatar-errors" class="center-block" style="width:800px;display:none;"></div>
								<div id="avatar-container">
									<input type="file" id="avatar" name="photo" class="file-loading">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="user_rfid" class="col-lg-3 control-label">Code carte</label>
							<div class="col-lg-9">
								<div class="input-group">
									<input type="text" name="user_rfid" class="form-control" placeholder="Scannez une nouvelle puce pour récupérer le code RFID" value="<?php echo $details["user_rfid"];?>">
									<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" name="fetch-rfid">Lancer la détection</a></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="rue" class="col-lg-3 control-label">Adresse postale</label>
							<div class="col-lg-9">
								<input type="text" name="rue" id="rue" placeholder="Adresse" class="form-control" value="<?php echo $details["rue"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="code_postal" class="col-lg-3 control-label">Code postal</label>
							<div class="col-lg-9">
								<input type="number" name="code_postal" id="code_postal" placeholder="Code Postal" class="form-control" value="<?php echo $details["code_postal"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="ville" class="col-lg-3 control-label">Ville</label>
							<div class="col-lg-9">
								<input type="text" name="ville" id="ville" placeholder="Ville" class="form-control" value="<?php echo $details["ville"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="telephone" class="col-lg-3 control-label">Téléphone principal</label>
							<div class="col-lg-9">
								<input type="tel" name="telephone" id="telephone" placeholder="Numéro de téléphone" class="form-control" value="<?php echo $details["telephone"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="tel_secondaire" class="col-lg-3 control-label">Téléphone secondaire</label>
							<div class="col-lg-9">
								<input type="tel" name="tel_secondaire" id="tel_secondaire" placeholder="Numéro de téléphone secondaire" class="form-control" value="<?php echo $details["tel_secondaire"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="date_naissance" class="col-lg-3 control-label">Date de naissance</label>
							<div class="col-lg-9">
								<input type="date" name="date_naissance" id="date_naissance" class="form-control" value="<?php echo $details["date_naissance"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="commentaires" class="col-lg-3 control-label">Commentaires</label>
							<div class="col-lg-9">
								<textarea rows="5" class="form-control" name="commentaires"><?php echo $details["commentaires"];?></textarea>
							</div>
						</div>
						<input type="submit" name="edit" role="button" class="btn btn-primary btn-block" value="ENREGISTRER LES MODIFICATIONS">
					</form>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script src="assets/js/fileinput.min.js"></script>
		<script>
			$("#avatar").fileinput({
				overwriteInitial: true,
				maxFileSize: 3000,
				showClose: false,
				showCaption: false,
				browseLabel: '',
				removeLabel: '',
				browseIcon: '<i class="glyphicon glyphicon-folder-open"></i>',
				removeTitle: 'Cancel or reset changes',
				elErrorContainers: '#kv-avatar-errors',
				elPreviewContainer: '#avatar-container',
				msgErrorClass: 'alert alert-block alert-danger',
				defaultPreviewContent: '<img src="<?php echo $details["photo"];?>" style="width:118px;">',
				layoutTemplates: {main2: '{preview} {browse}' },
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

			$("[name='link-forfait']").click(function(){
				$("[name='forfaits-actifs']").show();
				$("[name='link-forfait']").hide();
			});

			$("[name='forfaits-actifs']").blur(function(){
				var clicked = $(this);
				var eleve_id = <?php echo $data;?>;
				var produit_id = clicked.val();
				var cours_id = clicked.prev().val();
				$.post("functions/link_forfait.php", {eleve_id : eleve_id, cours_id : cours_id, produit_id : produit_id}).done(function(data){
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
				$.post("functions/add_tarif_prof.php", {prof_id : prof_id, prestation : prestation, tarif : tarif, ratio : ratio}).success(function(data){
					$("#new-tarif").hide();
					showSuccessNotif(data);
					$(".fetched").remove();
					fetchTarifs();
				})
			};

			function fetchTarifs(){
				var id = $("#prof_id").val();
				$.post("functions/get_tarifs.php", {id : id}).done(function(data){
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
				$.post("functions/update_tarif_prof.php", {update_id : update_id, tarif : tarif}).done(function(data){
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
				$.post("functions/delete_tarif_prof.php", {delete_id : delete_id}).done(function(data){
					showSuccessNotif(data);
					$(".fetched").remove();
					fetchTarifs();
				});
			}
			<?php } ?>
		</script>
	</body>
</html>
