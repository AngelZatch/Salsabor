<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$valueTeacher = 0;
$valueEleve = 0;
$valueStaff = 0;
$backLink = "";
$titleText = "Réaliser une inscription";

$now = date_create('now')->format('Y-m-d');

$connaissances = $db->query("SELECT * FROM sources_connaissance");

if(isset($_POST['addAdherent'])){
	// Upload de l'image

	// Champs par défaut
	$actif = 1;
	$acces_web = 1;
	if($_FILES["profile-picture"]["name"]){
		$target_dir = "assets/pictures/";
		$target_file = $target_dir.basename($_FILES["profile-picture"]["name"]);
		$picture = $target_dir.$data.".".pathinfo($_FILES["profile-picture"]["name"], PATHINFO_EXTENSION);
		move_uploaded_file($_FILES["profile-picture"]["tmp_name"], $picture);
		try{
			$db->beginTransaction();
			$new = $db->prepare('INSERT INTO users(user_prenom, user_nom, user_rfid, date_naissance,
												date_inscription, rue, code_postal, ville, mail,
												telephone, tel_secondaire, photo, source_connaissance,
												acces_web, est_membre, est_professeur, est_staff, est_prestataire,
												est_autre, autre_statut, user_rib, actif, commentaires)
										VALUES(:prenom, :nom, :rfid, :date_naissance,
												:date_inscription, :rue, :code_postal, :ville, :mail,
												:telephone, :tel_secondaire, :photo, :sources_connaissance,
												:acces_web, :est_membre, :est_professeur, :est_staff, :est_prestataire,
												:est_autre, :autre_statut, :user_rib, :actif, :commentaires)');
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
			$new->bindParam(':photo', $picture);
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
			$new->bindParam(':commentaires', $_POST["commentaires"]);
			$new->execute();
			if(isset($_POST["rfid"])){
				$delete = $db->prepare('DELETE FROM participations WHERE user_rfid=? AND status=1');
				$delete->bindParam(1, $_POST["rfid"]);
				$delete->execute();
			}
			$db->commit();
			header('Location: dashboard');
		} catch(PDOException $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	} else {
		try{
			$db->beginTransaction();
			$new = $db->prepare('INSERT INTO users(user_prenom, user_nom, user_rfid, date_naissance,
												date_inscription, rue, code_postal, ville, mail,
												telephone, tel_secondaire, source_connaissance,
												acces_web, est_membre, est_professeur, est_staff, est_prestataire,
												est_autre, autre_statut, user_rib, actif, commentaires)
										VALUES(:prenom, :nom, :rfid, :date_naissance,
												:date_inscription, :rue, :code_postal, :ville, :mail,
												:telephone, :tel_secondaire, :sources_connaissance,
												:acces_web, :est_membre, :est_professeur, :est_staff, :est_prestataire,
												:est_autre, :autre_statut, :user_rib, :actif, :commentaires)');
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
			$new->bindParam(':commentaires', $_POST["commentaires"]);
			$new->execute();
			if(isset($_POST["rfid"])){
				$delete = $db->prepare('DELETE FROM participations WHERE user_rfid=? AND status=1');
				$delete->bindParam(1, $_POST["rfid"]);
				$delete->execute();
			}
			$db->commit();
			header('Location: dashboard');
		} catch(PDOException $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
}

if(isset($_POST['addSell'])){
	// Upload de l'image

	// Champs par défaut
	$actif = 1;
	$acces_web = 1;
	if($_FILES["profile-picture"]["name"]){
		$target_dir = "assets/pictures/";
		$target_file = $target_dir.basename($_FILES["profile-picture"]["name"]);
		$picture = $target_dir.$data.".".pathinfo($_FILES["profile-picture"]["name"], PATHINFO_EXTENSION);
		move_uploaded_file($_FILES["profile-picture"]["tmp_name"], $picture);
		try{
			$db->beginTransaction();
			$new = $db->prepare('INSERT INTO users(user_prenom, user_nom, user_rfid, date_naissance,
												date_inscription, rue, code_postal, ville, mail,
												telephone, tel_secondaire, photo, source_connaissance,
												acces_web, est_membre, est_professeur, est_staff, est_prestataire,
												est_autre, autre_statut, user_rib, actif, commentaires)
										VALUES(:prenom, :nom, :rfid, :date_naissance,
												:date_inscription, :rue, :code_postal, :ville, :mail,
												:telephone, :tel_secondaire, :photo, :sources_connaissance,
												:acces_web, :est_membre, :est_professeur, :est_staff, :est_prestataire,
												:est_autre, :autre_statut, :user_rib, :actif, :commentaires)');
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
			$new->bindParam(':photo', $picture);
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
			$new->bindParam(':commentaires', $_POST["commentaires"]);
			$new->execute();
			if(isset($_POST["rfid"])){
				$delete = $db->prepare('DELETE FROM participations WHERE user_rfid=? AND status=1');
				$delete->bindParam(1, $_POST["rfid"]);
				$delete->execute();
			}
			$db->commit();
			header('Location: catalogue.php?user='.$id.'');
		} catch(PDOException $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	} else {
		try{
			$db->beginTransaction();
			$new = $db->prepare('INSERT INTO users(user_prenom, user_nom, user_rfid, date_naissance,
												date_inscription, rue, code_postal, ville, mail,
												telephone, tel_secondaire, source_connaissance,
												acces_web, est_membre, est_professeur, est_staff, est_prestataire,
												est_autre, autre_statut, user_rib, actif, commentaires)
										VALUES(:prenom, :nom, :rfid, :date_naissance,
												:date_inscription, :rue, :code_postal, :ville, :mail,
												:telephone, :tel_secondaire, :sources_connaissance,
												:acces_web, :est_membre, :est_professeur, :est_staff, :est_prestataire,
												:est_autre, :autre_statut, :user_rib, :actif, :commentaires)');
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
			$new->bindParam(':commentaires', $_POST["commentaires"]);
			$new->execute();
			if(isset($_POST["rfid"])){
				$delete = $db->prepare('DELETE FROM participations WHERE user_rfid=? AND status=1');
				$delete->bindParam(1, $_POST["rfid"]);
				$delete->execute();
			}
			$db->commit();
			header('Location: catalogue.php?user='.$id.'');
		} catch(PDOException $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Inscription d'un adhérent | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-pencil"></span> <?php echo $titleText;?></legend>
					<form action="" method="post" class="form-horizontal" role="form" id="add_adherent" enctype="multipart/form-data">
						<p class="form-section">Informations personnelles</p>
						<div class="form-group">
							<label for="identite_prenom" class="col-lg-3 control-label">Prénom</label>
							<div class="col-sm-9">
								<input type="text" name="identite_prenom" id="identite_prenom" class="form-control mandatory" placeholder="Prénom">
							</div>
						</div>
						<div class="form-group">
							<label for="identite_nom" class="col-lg-3 control-label">Nom</label>
							<div class="col-sm-9">
								<input type="text" name="identite_nom" id="identite_nom" class="form-control mandatory" placeholder="Nom de famille">
							</div>
						</div>
						<div class="form-group">
							<label for="mail" class="col-lg-3 control-label">Adresse mail</label>
							<div class="col-lg-9">
								<input type="email" name="mail" id="mail" placeholder="Adresse mail" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="avatar" class="col-lg-3 control-label">Photo de profil</label>
							<div class="col-lg-9">
								<div id="kv-avatar-errors" class="center-block" style="width:800px;display:none;"></div>
								<div id="avatar-container">
									<input type="file" id="avatar" name="profile-picture" class="file-loading">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="rue" class="col-lg-3 control-label">Adresse postale</label>
							<div class="col-lg-9">
								<input type="text" name="rue" id="rue" placeholder="Adresse" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="code_postal" class="col-lg-3 control-label">Code postal</label>
							<div class="col-lg-9">
								<input type="number" name="code_postal" id="code_postal" placeholder="Code Postal" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="ville" class="col-lg-3 control-label">Ville</label>
							<div class="col-lg-9">
								<input type="text" name="ville" id="ville" placeholder="Ville" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="telephone" class="col-lg-3 control-label">Téléphone principal</label>
							<div class="col-lg-9">
								<input type="tel" name="telephone" id="telephone" placeholder="Numéro de téléphone" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="tel_secondaire" class="col-lg-3 control-label">Téléphone secondaire</label>
							<div class="col-lg-9">
								<input type="tel" name="tel_secondaire" id="tel_secondaire" placeholder="Numéro de téléphone secondaire" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="date_naissance" class="col-lg-3 control-label">Date de naissance</label>
							<div class="col-lg-9">
								<input type="date" name="date_naissance" id="date_naissance" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<label for="commentaires" class="col-lg-3 control-label">Commentaires</label>
							<div class="col-lg-9">
								<textarea rows="5" class="form-control" name="commentaires"></textarea>
							</div>
						</div>
						<!--						<div class="row">
<div class="col-lg-6">
<div class="form-group">
<label for="certificat_medical" class="control-label">Certificat Médical</label>
<input type="file" class="form-control" name="certificat_medical">
</div>
</div>
</div>-->
						<p class="form-section">Informations Salsabor</p>
						<div class="form-group">
							<label for="date_inscription" class="col-lg-3 control-label">Date d'inscription</label>
							<div class="col-lg-9">
								<input type="date" name="date_inscription" id="date_inscription" class="form-control mandatory" value="<?php echo $now;?>">
								<span class="label-tip">Par défaut, aujourd'hui</span>
							</div>
						</div>
						<div class="form-group">
							<label for="rfid" class="col-lg-3 control-label">Code carte</label>
							<div class="col-lg-9">
								<div class="input-group">
									<input type="text" name="rfid" class="form-control" placeholder="Scannez une nouvelle puce pour récupérer le code RFID">
									<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" name="fetch-rfid">Lancer la détection</a></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="statuts" class="col-lg-3 control-label">Statut(s) du contact</label>
							<div class="col-lg-9">
								<label for="est_membre" class="control-label">Membre</label>
								<input name="est_membre" id="est_membre" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $valueEleve;?>">
								<label for="est_professeur" class="control-label">Professeur</label>
								<input name="est_professeur" id="est_professeur" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $valueTeacher;?>">
								<label for="est_staff" class="control-label">Staff</label>
								<input name="est_staff" id="est_staff" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $valueStaff;?>">
								<label for="est_prestataire" class="control-label">Prestataire</label>
								<input name="est_prestataire" id="est_prestataire" class="rib-toggle" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
								<label for="est_autre" class="contorl-label">Autre <span class="label-tip">Spécifiez ci-dessous</span></label>
								<input name="est_autre" id="est_autre" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0"><br>
								<span class="label-tip">Cochez autant que nécessaire</span>
							</div>
						</div>
						<div class="form-group" id="autre" style="display:none;">
							<label for="autre_statut" class="col-lg-3 control-label">Autre statut</label>
							<div class="col-lg-9">
								<input type="text" name="autre_statut" class="form-control">
							</div>
						</div>
						<div class="form-group" id="rib-data" style="display:none;">
							<label for="rib" class="col-lg-3 control-label">Informations bancaires</label>
							<div class="col-lg-9">
								<input type="text" name="rib" class="form-control">
								<span class="label-tip">Pour un professeur, un staff ou un prestataire</span>
							</div>
						</div>
						<div class="form-group">
							<label for="sources_connaissance" class="col-lg-3 control-label">D'où connaissez-vous Salsabor ?</label>
							<div class="col-lg-9">
								<select name="sources_connaissance" class="form-control">
									<?php while($sources = $connaissances->fetch(PDO::FETCH_ASSOC)){ ?>
									<option value="<?php echo $sources["source_id"];?>"><?php echo $sources["source"];?></option>
									<?php } ?>
								</select>
								<span class="label-tip">Sélectionnez la source la plus influente</span>
							</div>
						</div>
						<div class="col-lg-6">
							<input type="submit" name="addAdherent" role="button" class="btn btn-primary submit-button btn-block" value="Enregistrer" disabled>
						</div>
						<div class="col-lg-6">
							<input type="submit" name="addSell" role="button" class="btn btn-primary submit-button btn-block" value="Enregistrer et acheter" disabled>
						</div>
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
				defaultPreviewContent: '<img src="assets/images/logotype-white.png" style="width:118px;">',
				layoutTemplates: {main2: '{preview} {browse}' },
			});
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
		</script>
	</body>
</html>
