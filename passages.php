<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d H:i:s');
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));
$queryNextCours = $db->prepare("SELECT * FROM cours
								JOIN salle ON cours_salle=salle.salle_id
								JOIN users ON prof_principal=users.user_id
								JOIN niveau ON cours_niveau=niveau.niveau_id
								WHERE (cours_start>=? AND cours_start<=? AND ouvert=1) OR ouvert=1
								ORDER BY cours_start ASC, cours_id ASC");
$queryNextCours->bindParam(1, $compare_start);
$queryNextCours->bindParam(2, $compare_end);
$queryNextCours->execute();

$queryEleves = $db->query("SELECT * FROM users ORDER BY user_nom ASC");
$array_eleves = array();
while($eleves = $queryEleves->fetch(PDO::FETCH_ASSOC)){
	array_push($array_eleves, $eleves["user_prenom"]." ".$eleves["user_nom"]);
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Passages | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-map-marker"></span> Passages</p>
					</div>
					<div class="col-lg-6">
						<p id="current-time"></p>
					</div>
				</div>
				<div class="col-sm-10 main">
					<p id="last-edit"><?php echo ($queryNextCours->rowCount()!=0)?"".$queryNextCours->rowCount()." cours sont actuellement ouvert(s) aux enregistrements":"Aucun cours n'est à venir";?></p>
					<?php
while($nextCours = $queryNextCours->fetch(PDO::FETCH_ASSOC)){
	$queryPassages = $db->prepare("SELECT * FROM passages
							JOIN lecteurs_rfid ON passage_salle=lecteurs_rfid.lecteur_ip
							JOIN users ON passage_eleve=users.user_rfid OR passage_eleve_id=users.user_id
							WHERE ((status=0 OR status=3) AND lecteur_lieu=? AND passage_date>=? AND passage_date<=?) OR (status=2 AND cours_id=?)
							ORDER BY user_nom ASC");
	$queryPassages->bindParam(1, $nextCours["cours_salle"]);
	$queryPassages->bindParam(2, date("Y-m-d H:i:s", strtotime($nextCours["cours_start"].'-30MINUTES')));
	$queryPassages->bindParam(3, date("Y-m-d H:i:s", strtotime($nextCours["cours_start"].'+30MINUTES')));
	$queryPassages->bindParam(4, $nextCours["cours_id"]);
	$queryPassages->execute();
					?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<div class="panel-title">
								<div class="cours-infos">
									<div class="row">
										<p class="cours-title col-lg-4">
											<?php echo $nextCours["cours_intitule"];?> <a href="cours_edit.php?id=<?php echo $nextCours["cours_id"];?>&drive=passages" class="small-link">>> Détails...</a>
										</p>
										<p class="col-lg-8">
											<span class="glyphicon glyphicon-time"></span> <?php echo "Le ".date_create($nextCours["cours_start"])->format("d/m")." de ".date_create($nextCours["cours_start"])->format("H:i")." - ".date_create($nextCours["cours_end"])->format("H:i");?> (<span class="relative-start"><?php echo date_create($nextCours["cours_start"])->format("Y-m-d H:i");?></span>)
										</p>
									</div>
									<div class="row">
										<p class="col-lg-2 col-lg-offset-4">
											<span class="glyphicon glyphicon-signal"></span> <?php echo $nextCours["niveau_name"];?>
										</p>
										<p class="col-lg-3">
											<span class="glyphicon glyphicon-pushpin"></span> <?php echo $nextCours["salle_name"];?>
										</p>
										<p class="col-lg-2">
											<span class="glyphicon glyphicon-blackboard"></span> <?php echo $nextCours["user_prenom"]." ".$nextCours["user_nom"];?>
										</p>
									</div>
								</div>
								<p class="list-item-option close-cours">
									<span class="glyphicon glyphicon-ok-sign" title="Fermer le cours"></span> FERMER
									<input type="hidden" id="cours-id" value="<?php echo $nextCours["cours_id"];?>">
								</p>
								<!--<p class="list-item-option validate-all">
<span class="glyphicon glyphicon-floppy-saved" title="Valider tous les enregistrements"></span> VALIDER TOUS
</p>-->
								<p class="list-item-option close-record-display">
									<span class="glyphicon glyphicon-resize-small"></span> REDUIRE
								</p>
								<p id="cours-people">Actuellement <span class="cours-count"></span> participants, dont <span class="cours-count-checked">0</span> validés.</p>
							</div>
						</div>
						<ul class="list-group">
							<?php
	while($passages = $queryPassages->fetch(PDO::FETCH_ASSOC)){
		switch($passages["status"]){
			case 0:
			$status = "default";
			break;

			case 2:
			$status = "success";
			break;

			case 3:
			$status = "danger";
			break;
		};
		$queryEcheances = $db->query("SELECT * FROM produits_echeances JOIN transactions ON reference_achat=transactions.id_transaction WHERE echeance_effectuee=2 AND payeur_transaction=$passages[user_id]")->rowCount();
							?>
							<li class="list-group-item list-group-item-<?php echo $status;?> col-sm-12">
								<p class="col-lg-3 eleve-infos">
									<?php echo $passages["user_prenom"]." ".$passages["user_nom"];?>
									<input type="hidden" class="eleve-id" value="<?php echo $passages["user_id"];?>">
									<input type="hidden" class="passage-id" value="<?php echo $passages["passage_id"];?>">
								</p>
								<p class="col-lg-1 eleve-tag">
									<?php echo ($passages["passage_eleve"] != "")?$passages["passage_eleve"]:"Pas de carte";?>
								</p>
								<p class="col-lg-2">Enregsitré à <?php echo date_create($passages["passage_date"])->format("H:i:s");?></p>
								<div class="col-lg-6 record-options">
									<?php if ($passages["status"] == 0 || $passages["status"] == 3){?>
									<p class="list-item-option validate-record" title="Valider l'enregistrement comme étant bien pour ce cours">
										<span class="glyphicon glyphicon-ok"></span> VALIDER
									</p>
									<p class="list-item-option move-record" data-toggle="popover-x" 	data-trigger="focus" data-placement="bottom bottom-right" data-target="#popoverPassages" title="Assigner le passage à un autre cours">
										<span class="glyphicon glyphicon-circle-arrow-right"></span> DEPLACER
									</p>
									<div class="popover popover-primary popover-lg" id="popoverPassages">
										<div class="arrow"></div>
										<div class="popover-title"><span class="close" data-dismiss="popover-x">&times;</span>Sélectionnez le cours à attribuer</div>
										<div class="popover-content content-passages"></div>
									</div>
									<!--<p class="list-item-option move-bundle" data-toggle="popover-x" data-target="#popoverForfaits" data-trigger="focus" data-placement="bottom bottom-right" title="Modifier le forfait utilisé" id="<?php echo $passages["user_id"];?>">
										<span class="glyphicon glyphicon-credit-card"></span> FORFAIT
									</p>-->
									<div class="popover popover-primary popover-lg" id="popoverForfaits">
										<div class="arrow"></div>
										<div class="popover-title"><span class="close" data-dismiss="popover-x">&times;</span>Sélectionnez le forfait à utiliser</div>
										<div class="popover-content content-forfaits"></div>
									</div>
									<a href="actions/validate_deletion.php" data-title="Suppression de passage" data-toggle="lightbox" data-gallery="remoteload" class="list-item-option delete-trigger" title="Supprimer ce passage">
										<span class="glyphicon glyphicon-trash"></span> SUPPRIMER
									</a>
									<?php } else if($passages["status"] == 2) {  ?>
									<p class="list-item-option unvalidate-record">
										<span class="glyphicon glyphicon-remove" title="Annuler la validation de cet enregistrement"></span> ANNULER
									</p>
									<?php } ?>
								</div>
							</li>
							<?php }?>
						</ul>
						<div class="panel-footer">
							<div class="input-group input-group-lg">
								<input type="text" for="liste_participants" class="form-control liste-participants has-name-completion" placeholder="Ajouter un participant par passage">
								<span role="buttton" class="input-group-btn add-eleve"><a class="btn btn-info" role="button">Ajouter l'élève</a></span>
							</div>
						</div>
					</div>
					<?php }?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<div class="panel-title">
								<p>Passages non traités</p>
								<p class="list-item-option close-record-display">
									<span class="glyphicon glyphicon-resize-small"></span> REDUIRE
								</p>
							</div>
						</div>
						<ul class="list-group">
							<?php
$queryUnidentified = $db->query("SELECT * FROM passages p
							JOIN users u ON passage_eleve = u.user_rfid
							WHERE status > 2
							ORDER BY passage_date DESC");
while($unidentified = $queryUnidentified->fetch(PDO::FETCH_ASSOC)){ ?>
							<li class="list-group-item" id="passage-<?php echo $unidentified["passage_id"];?>">
								<div class="row">
									<div class="col-lg-3">
										<p>
											<?php echo $unidentified["user_prenom"]." ".$unidentified["user_nom"];?>
										</p>
									</div>
									<div class="col-lg-1">
										<p><?php echo $unidentified["passage_eleve"];?></p>
									</div>
									<div class="col-lg-3">
										<p>Enregistré le <?php echo date_create($unidentified["passage_date"])->format('d/m')." à ".date_create($unidentified["passage_date"])->format('H:i:s');?></p>
									</div>
									<div class="col-lg-5">
										<!--<p class="list-item-option move-record" data-toggle="popover-x" data-target="#popoverPassages" data-trigger="focus" data-placement="bottom bottom-right" title="Assigner le passage à un cours">
											<span class="glyphicon glyphicon-circle-arrow-right"></span> ASSIGNER
										</p>-->
										<div class="popover popover-default popover-lg" id="popoverPassages">
											<div class="arrow"></div>
											<div class="popover-title"><span class="close" data-dismiss="popover-x">&times;</span>Sélectionnez le cours à attribuer</div>
											<div class="popover-content"></div>
										</div>
										<a href="actions/validate_deletion.php" data-title="Suppression de passage" data-toggle="lightbox" data-gallery="remoteload" class="list-item-option delete-trigger" title="Supprimer ce passage" id="<?php echo $unidentified["passage_id"];?>">
											<span class="glyphicon glyphicon-trash"></span> SUPPRIMER
										</a>
									</div>
								</div>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				var listeAdherents = JSON.parse('<?php echo json_encode($array_eleves);?>');
				$(".has-name-completion").autocomplete({
					source: listeAdherents
				});

				$(".add-eleve").click(function(){
					var clicked = $(this);
					var adherent = clicked.prev().val();
					var cours_id = clicked.closest(".panel").find("#cours-id").val();
					$.post("functions/add_record.php", {cours_id : cours_id, adherent : adherent}).done(function(data){
						window.location.href = "passages.php";
					});
				})
			}).on('click', '.validate-record', function(){
				var clicked = $(this);
				var cours_id = clicked.closest(".panel").find("#cours-id").val();
				var eleve_id = clicked.parents().siblings(".eleve-infos").children("input.eleve-id").val();
				var passage_id = clicked.parents().siblings(".eleve-infos").children("input.passage-id").val();
				var rfid = clicked.parents().siblings(".eleve-tag").html();
				$.post("functions/validate_record.php", {cours_id : cours_id, eleve_id : eleve_id, passage_id : passage_id, rfid : rfid}).done(function(data){
					clicked.closest("li").removeClass('list-group-item-warning');
					clicked.closest("li").addClass("list-group-item-success");
					showSuccessNotif(data);
					window.location.href = "passages.php";
				});
			}).on('click', '.move-record', function(){
				var clicked = $(this);
				console.log($(this).offset());
				$("#popoverPassages").popoverX('refreshPosition');
				window.passage_id = clicked.parents().siblings(".eleve-infos").children("input.passage-id").val();
				$.post("functions/fetch_target_cours.php", {passage_id : passage_id}).done(function(data){
					$(".content-passages").empty();
					var res = JSON.parse(data);
					var line = "";
					for(var i = 0; i < res.length; i++){
						line += "<div class='panel panel-default panel-target'>";
						line += "<input type='hidden' value="+res[i].id+">";
						line += "<div class='panel-heading'>";
						line += "<div class='row'>";
						line += "<div class='col-lg-7'><span class='glyphicon glyphicon-eye-open'></span> "+res[i].nom+"</div>";
						line += "<div class='col-lg-5'><span class='glyphicon glyphicon-blackboard'></span> "+res[i].prof+"</div>";
						line += "</div>";
						line += "</div>";
						line += "<div class='panel-body'>";
						line += "<div class='row'>";
						line += "<div class='col-lg-4'><span class='glyphicon glyphicon-signal'></span> "+res[i].niveau+"</div>";
						line += "<div class='col-lg-4'><span class='glyphicon glyphicon-time'></span> "+res[i].heure+"</div>";
						line += "<div class='col-lg-4'><span class='glyphicon glyphicon-pushpin'></span> "+res[i].salle+"</div>";
						line += "</div>";
						line += "</div>";
						line += "</div>";
					}
					$(".content-passages").append(line);
				})
			}).on('click', '.panel-target', function(){
				var passage_id = window.passage_id;
				var target_id = $(this).children("input").val();
				$.post("functions/move_record.php", {passage_id : passage_id, target_id : target_id}).done(function(data){
					window.location.href = "passages.php";
				})
			}).on('click', '.unvalidate-record', function(){
				var clicked = $(this);
				var cours_id = clicked.closest(".panel").find("#cours-id").val();
				var eleve_id = clicked.parents().siblings(".eleve-infos").children("input.eleve-id").val();
				var passage_id = clicked.parents().siblings(".eleve-infos").children("input.passage-id").val();
				var rfid = clicked.parents().siblings(".eleve-tag").html();
				$.post("functions/unvalidate_record.php", {cours_id : cours_id, eleve_id : eleve_id, passage_id : passage_id, rfid : rfid}).done(function(data){
					window.location.href = "passages.php";
				});
			}).on('click', '.delete-trigger', function(){
				var passage_id = $(this).parents().siblings(".eleve-infos").children("input.passage-id").val();
				if(passage_id == null){
					window.passage_id = $(this).attr("id");
				} else {
					window.passage_id = passage_id;
				}
			}).on('click', '.delete-record', function(){
				$.post("functions/delete_record.php", {passage_id : window.passage_id}).done(function(){
					window.location.href = "passages.php";
				});
			}).on('click', '.close-record-display', function(){
				$(this).parent().parent().next().slideToggle('600');
				$(this).empty();
				$(this).append("<span class='glyphicon glyphicon-resize-full'></span> MONTRER");
				$(this).removeClass("close-record-display");
				$(this).addClass("open-record-display");
			}).on('click', '.open-record-display', function(){
				$(this).parent().parent().next().slideToggle('600');
				$(this).empty();
				$(this).append("<span class='glyphicon glyphicon-resize-small'></span> REDUIRE");
				$(this).addClass("close-record-display");
				$(this).removeClass("open-record-display");
			}).on('click', '.move-bundle', function(){
				var eleve_id = $(this).attr("id");
				$.post("functions/fetch_target_forfait.php", {eleve_id : eleve_id}).done(function(data){
					$(".content-forfaits").empty();
					var res = JSON.parse(data);
					var line = "";
					for(var i = 0; i < res.length; i++){
						if(res[i].actif == 1){
							line += "<div class='panel panel-success panel-target'>";
						} else {
							line += "<div class='panel panel-default panel-target'>";
						}
						line += "<input type='hidden' value="+res[i].id+">";
						line += "<div class='panel-heading'>";
						line += "<div class='row'>";
						line += "<div class='col-lg-7'><span class='glyphicon glyphicon-credit-card'></span> "+res[i].nom+"</div>";
						line += "</div>";
						line += "</div>";
						line += "<div class='panel-body'>";
						line += "<div class='row'>";
						line += "<div class='col-lg-7'><span class='glyphicon glyphicon-time'></span> "+res[i].validite+"</div>";
						line += "<div class='col-lg-5'><span class='glyphicon glyphicon-scale'></span> "+res[i].solde+"</div>";
						line += "</div>";
						line += "</div>";
						line += "</div>";
					}
					$(".content-forfaits").append(line);
				})
			})
			$(".close-cours").click(function(){
				var cours = $(this).closest(".panel").find("#cours-id").val();
				if($(this).closest(".panel").find(".cours-count").html() != $(this).closest(".panel").find(".cours-count-checked").html()){
					$.notify("Impossible de fermer un cours dont les passages n'ont pas tous été validés.", {globalPosition: "right bottom", className:"error"});
				} else {
					$.post("functions/close_cours.php", {cours : cours}).done(function(data){
						showSuccessNotif(data);
					});
					$(this).closest(".panel").hide('200');
				}
			});

			$(".validate-all").click(function(){
				var cours = $(this).children("input").val();
				var clicked = $(this);

			});
		</script>
	</body>
</html>
