<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$user_id = $_GET['id'];

// User details
$details = $db->query("SELECT * FROM users u
						WHERE user_id='$user_id'")->fetch(PDO::FETCH_ASSOC);

$labels = $db->query("SELECT * FROM assoc_user_tags ur
						JOIN tags_user tu ON ur.tag_id_foreign = tu.rank_id
						WHERE user_id_foreign = '$user_id'
						ORDER BY tag_color DESC");

$details["count"] = $db->query("SELECT * FROM tasks
					WHERE ((task_token LIKE '%USR%' AND task_target = '$user_id')
					OR (task_token LIKE '%PRD%' AND task_target IN (SELECT id_produit_adherent FROM produits_adherents WHERE id_user_foreign = '$user_id'))
					OR (task_token LIKE '%TRA%' AND task_target IN (SELECT id_transaction FROM transactions WHERE payeur_transaction = '$user_id')))
						AND task_state = 0")->rowCount();

$is_teacher = $db->query("SELECT * FROM assoc_user_tags ur
								JOIN tags_user tu ON tu.rank_id = ur.tag_id_foreign
								WHERE rank_name = 'Professeur' AND user_id_foreign = '$user_id'")->rowCount();

// If the user is a teacher
/*if($is_teacher == 1){
	// On obtient l'historique de ses cours
	$queryHistoryDonnes = $db->prepare('SELECT * FROM sessions s JOIN rooms r ON s.session_room = r.room_id WHERE session_teacher=? ORDER BY session_start ASC');
	$queryHistoryDonnes->bindValue(1, $user_id);
	$queryHistoryDonnes->bindValue(2, $user_id);
	$queryHistoryDonnes->execute();

	// Tarifs
	$queryTarifs = $db->prepare('SELECT * FROM tarifs_professeurs JOIN prestations ON type_prestation=prestations.prestations_id WHERE prof_id_foreign=?');
	$queryTarifs->bindValue(1, $user_id);
	$queryTarifs->execute();

	// Prestations
	$queryPrestations = $db->query('SELECT * FROM prestations WHERE est_cours=1');

	// Types de ratio multiplicatif
	$ratio = $db->query("SHOW COLUMNS FROM tarifs_professeurs LIKE 'ratio_multiplicatif'");

	// Prix de tous les cours
	$totalPrice = 0;
	$totalPaid = 0;
	$totalDue = 0;
}*/

// Et on cherche à savoir si des échéances sont en retard
$queryEcheances = $db->query("SELECT * FROM produits_echeances JOIN transactions ON reference_achat=transactions.id_transaction WHERE echeance_effectuee=2 AND payeur_transaction=$user_id")->rowCount();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Editer - <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<link href="assets/css/croppie.css" rel="stylesheet" type="text/css">
		<?php include "scripts.php";?>
		<script src="assets/js/tags.js"></script>
		<script src="assets/js/croppie.min.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<?php if($queryEcheances != 0){ ?>
					<div class="alert alert-danger"><strong>Attention !</strong> Cet adhérent a des échéances en retard.</div>
					<?php } ?>
					<ul class="nav nav-tabs">
						<li role="presentation" class="active visible-xs-block"><a href="user/<?php echo $user_id;?>">Infos perso</a></li>
						<li role="presentation" class="active hidden-xs"><a href="user/<?php echo $user_id;?>">Informations personnelles</a></li>
						<?php if($is_teacher == 1){ ?>
						<!--<li role="presentation"><a>Cours donnés</a></li>-->
						<li role="presentation"><a href="user/<?php echo $user_id;?>/tarifs">Tarifs</a></li>
						<!--<li role="presentation"><a>Statistiques</a></li>-->
						<?php } ?>
						<li role="presentation"><a href="user/<?php echo $user_id;?>/abonnements">Abonnements</a></li>
						<li role="presentation"><a href="user/<?php echo $user_id;?>/historique">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $user_id;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $user_id;?>/reservations">Réservations</a></li>
						<li role="presentation"><a href="user/<?php echo $user_id;?>/taches">Tâches</a></li>
					</ul>
					<form method="post" class="form-horizontal" role="form" id="user-details-form">
						<div class="form-group">
							<label for="statuts" class="col-lg-3 control-label">&Eacute;tiquettes</label>
							<div class="col-sm-9 user_tags">
								<h4>
									<?php while($label = $labels->fetch(PDO::FETCH_ASSOC)){ ?>
									<span class="label label-salsabor label-clickable label-deletable" title="Supprimer l'étiquette" id="user-tag-<?php echo $label["entry_id"];?>" data-target="<?php echo $label["entry_id"];?>" data-targettype='user' style="background-color:<?php echo $label["tag_color"];?>"><?php echo $label["rank_name"];?></span>
									<?php } ?>
									<span class="label label-default label-clickable label-add trigger-sub" id="label-add" data-subtype='user-tags' data-targettype='user' title="Ajouter une étiquette">+</span>
								</h4>
							</div>
						</div>
						<div class="form-group">
							<label for="avatar" class="col-sm-3 control-label">Photo de profil</label>
							<div class="col-sm-9">
								<div class="pp-input btn btn-primary">
									<span>Choisissez une image</span>
									<input type="file" id="upload" accept="image/jpeg, image/x-png">
								</div>
							</div>
							<!--<p class="help-block">Formats JPEG ou PNG et de taille inférieurs à 1 Mo.</p>-->
							<div class="crop-step">
								<div id="upload-demo"></div>
								<input type="hidden" id="imagebase64">
								<span class="btn btn-primary btn-block upload-result">Mettre à jour</span>
							</div>
						</div>
						<div class="form-group">
							<label for="user_rfid" class="col-sm-3 control-label">Code carte</label>
							<div class="col-sm-9">
								<div class="input-group">
									<input type="text" name="user_rfid" id="user-rfid" class="form-control" placeholder="Scannez une nouvelle puce pour récupérer le code RFID" value="<?php echo $details["user_rfid"];?>">
									<span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" name="fetch-rfid">Lancer la détection</a></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="rue" class="col-sm-3 control-label">Adresse postale</label>
							<div class="col-sm-9">
								<input type="text" name="rue" id="rue" placeholder="Adresse" class="form-control" value="<?php echo $details["rue"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="code_postal" class="col-sm-3 control-label">Code postal</label>
							<div class="col-sm-9">
								<input type="number" name="code_postal" id="code_postal" placeholder="Code Postal" class="form-control" value="<?php echo $details["code_postal"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="ville" class="col-sm-3 control-label">Ville</label>
							<div class="col-sm-9">
								<input type="text" name="ville" id="ville" placeholder="Ville" class="form-control" value="<?php echo $details["ville"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="tel_secondaire" class="col-sm-3 control-label">Téléphone secondaire</label>
							<div class="col-sm-9">
								<input type="tel" name="tel_secondaire" id="tel_secondaire" placeholder="Numéro de téléphone secondaire" class="form-control" value="<?php echo $details["tel_secondaire"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="website" class="col-sm-3 control-label">Site Web</label>
							<div class="col-sm-9">
								<input type="url" name="website" placeholder="Adresse de site web" class="form-control" value="<?php echo $details["website"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="organisation" class="col-sm-3 control-label">Organisation</label>
							<div class="col-sm-9">
								<input type="text" name="organisation" placeholder="Organisation" class="form-control" value="<?php echo $details["organisation"];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="date_naissance" class="col-sm-3 control-label">Date de naissance</label>
							<div class="col-sm-9">
								<input type="text" name="date_naissance" id="birthdate" class="form-control" placeholder="Date de naissance">
							</div>
						</div>
						<div class="form-group">
							<label for="commentaires" class="col-sm-3 control-label">Commentaires</label>
							<div class="col-sm-9">
								<textarea rows="5" class="form-control" name="commentaires"><?php echo $details["commentaires"];?></textarea>
							</div>
						</div>
					</form>
					<button class="btn btn-primary btn-block save-settings" id="update-user">Enregistrer les modifications</button>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<?php include "inserts/edit_modal.php";?>
		<style>
			.profile-picture{
				float: left;
				display: none;
			}
			.pp-input{
				cursor: pointer;
				position: relative;
			}
			.pp-input > input{
				position: absolute;
				top: 0;
				left: 0;
				opacity: 0;
				cursor: pointer;
				width: 100%;
				height: 100%;
			}
			.crop-step{
				display: none;
			}
			.user-pp{
				margin-bottom: 10px;
			}
		</style>
		<script>
			$(document).ready(function(){
				$("#birthdate").datetimepicker({
					format: "DD/MM/YYYY",
					defaultDate: "<?php echo (isset($details["date_naissance"]))?date_create($details['date_naissance'])->format("m/d/Y"):false;?>",
					locale: "fr",
				});

				// Croppie
				var $uploadCrop;

				function readFile(input) {
					if (input.files && input.files[0]) {
						var reader = new FileReader();
						reader.onload = function (e) {
							$uploadCrop.croppie('bind', {
								url: e.target.result
							});
							$('.upload-demo').addClass('ready');
							$(".crop-step").show();
						}
						reader.readAsDataURL(input.files[0]);
					}
				}

				$uploadCrop = $('#upload-demo').croppie({
					viewport: {
						width: 200,
						height: 200,
						type: 'circle'
					},
					boundary: {
						width: 300,
						height: 300
					}
				});

				$('#upload').on('change', function () { readFile(this); });
				$('.upload-result').on('click', function (ev) {
					$uploadCrop.croppie('result', {
						type: 'canvas',
						size: 'original'
					}).then(function (resp) {
						$('#imagebase64').val(resp);
						$('#form').submit();
					});
				});

				//
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
							$("[name='user_rfid']").val(data);
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
					var eleve_id = <?php echo $user_id;?>;
					var product_id = clicked.val();
					var session_id = clicked.prev().val();
					$.post("functions/link_forfait.php", {eleve_id : eleve_id, session_id : session_id, product_id : product_id}).done(function(data){
						showSuccessNotif(data);
						clicked.parents("tr.warning").removeClass('warning');
						clicked.hide();
						clicked.parent().html(product_id);
					});
				});
			}).on('click', '.upload-result', function(){
				var picture_value = $("#imagebase64").val();
				var user_id = /([0-9]+)/.exec(top.location.pathname);
				$.post("functions/update_picture.php", {picture_value : picture_value, user_id : user_id[0]}).done(function(data){
					console.log(data);
					var d = new Date();
					$(".banner-profile-picture").attr("src", data+"?"+d.getTime());
					$(".crop-step").hide();
				})
			}).on('click', '#update-user', function(){
				var user_id = /([0-9]+)/.exec(top.location.pathname);
				var values = $("#user-details-form").serialize(), table = "users", entry_id = user_id[0];
				$.when(updateEntry(table, values, entry_id)).done(function(data){
					console.log(data);
					var rfid = $("#user-rfid").val();
					if(rfid != ""){
						$.post("functions/delete_association_record.php", {rfid : rfid});
					}
					showNotification("Modifications enregistrées", "success");
				})
			})
				<?php if($is_teacher == 1){?>
			/*
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

				var prof_id = <?php echo $user_id;?>;
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

			})

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
			*/
				<?php } ?>
		</script>
	</body>
</html>
