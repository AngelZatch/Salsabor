<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once 'functions/cours.php';
/** Récupération des valeurs dans la base de données des champs **/
$id = $_GET['id'];
$cours = $db->query("SELECT * FROM sessions s
							JOIN users u ON s.session_teacher = u.user_id
							WHERE session_id='$id'")->fetch(PDO::FETCH_ASSOC);

// Array of all the sessions from this parent.
$all = $db->query("SELECT session_id FROM sessions WHERE session_group = $cours[session_group]")->fetchAll(PDO::FETCH_COLUMN);
$count = sizeof($all);
$current = array_search($id, $all);
$all_js = json_encode($all);
$next_js = json_encode(array_slice($all, $current));
// Link to previous and next
if($all[$current] != reset($all)){
	$prev = $all[$current - 1];
}
if($all[$current] != end($all)){
	$next = $all[$current + 1];
}

$querySalles = $db->query("SELECT * FROM rooms");

$labels = $db->query("SELECT * FROM assoc_session_tags us
						JOIN tags_session ts ON us.tag_id_foreign = ts.rank_id
						WHERE session_id_foreign = '$id'
						ORDER BY tag_color DESC");

$user_labels = $db->query("SELECT * FROM tags_user");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Cours de <?php echo $cours['session_name'];?> (<?php echo date_create($cours['session_start'])->format('d/m/Y');?> : <?php echo date_create($cours['session_start'])->format('H:i')?> / <?php echo date_create($cours['session_end'])->format('H:i');?>) | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script src="assets/js/participations.js"></script>
		<script src="assets/js/tasks-js.php"></script>
		<script src="assets/js/tags.js"></script>
		<script src="assets/js/sessions.js"></script>
		<script src="assets/js/raphael-min.js"></script>
		<script src="assets/js/morris.min.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend>
						<span class="glyphicon glyphicon-eye-open"></span> <span class="session-name"><?php echo $cours['session_name'];?></span>
						<div class="btn-toolbar float-right">
							<?php if($count == '1'){ ?>
							<input type='submit' name='editOne' role='button' class='btn btn-success' value='Enregistrer les modifications'>
							<?php } else { ?>
							<a href='#save-options' class='btn btn-primary' role='button' data-toggle='collapse' aria-expanded='false' aria-controls='saveOptions'><span class="glyphicon glyphicon-ok"></span> Enregistrer</a>
							<?php } ?>
							<a href="#delete-options" role="button" class="btn btn-danger" data-toggle="collapse" aria-expanded="false" aria-controls="deleteOptions"><span class="glyphicon glyphicon-trash"></span> Supprimer</a>
							<input type="hidden" name="id" value="<?php echo $id;?>">
						</div>
					</legend>
					<div class="collapse" id="save-options">
						<div class="well">
							<span>Enregistrer...</span>
							<button class="btn btn-primary btn-edit" id="edit-one">Ce cours</button>
							<button class="btn btn-primary btn-edit" id="edit-next">Tous les suivants</button>
							<button class="btn btn-primary btn-edit" id="edit-all">Toute la série</button>
						</div>
					</div>
					<div class="collapse" id="delete-options">
						<div class="well">
							<span>Supprimer...</span>
							<button class="btn btn-danger btn-delete" id="delete-one">Ce cours</button>
							<button class="btn btn-danger btn-delete" id="delete-next">Tous les suivants</button>
							<button class="btn btn-danger btn-delete" id="delete-all">Toute la série</button>
						</div>
					</div>
					<div class="container-fluid session-nav">
						<div class="col-xs-4 col-sm-3">
							<?php if(isset($prev)){ ?>
							<a href="cours/<?php echo $prev;?>" class="sub-legend prev-session"><span class="glyphicon glyphicon-arrow-left"></span> Cours précédent</a>
							<?php } else { ?>
							<p class="sub-legend prev-session disabled"><span class="glyphicon glyphicon-arrow-left"></span> - </p>
							<?php } ?>
						</div>
						<div class="col-xs-4 col-sm-6">
							<p id="last-edit"><?php if($cours['last_edit_date'] != '0000-00-00 00:00:00') echo "Dernière modification le ".date_create($cours['last_edit_date'])->format('d/m/Y')." à ".date_create($cours['last_edit_date'])->format('H:i');?></p>
						</div>
						<div class="col-xs-4 col-sm-3">
							<?php if(isset($next)){ ?>
							<a href="cours/<?php echo $next;?>" class="sub-legend next-session float-right">Cours suivant <span class="glyphicon glyphicon-arrow-right"></span></a>
							<?php } else { ?>
							<p class="sub-legend next-session float-right disabled"> - <span class="glyphicon glyphicon-arrow-right"></span></p>
							<?php } ?>
						</div>
					</div>
					<p class="sub-legend">Détails</p>
					<form name="session_details" id="session_details" role="form" class="form-horizontal">
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Intitulé du cours</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="session_name" id="session_name_input" value="<?php echo $cours['session_name'];?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Professeur <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Vous pouvez régler les noms qui vous seront suggérés avec le sélecteur 'Suggérer parmi...'"></span></label>
							<div class="col-lg-9">
								<div class="input-group">
									<div class="input-group-btn">
										<button type="button" class="btn btn-default dropdown-toggle suggestion-text" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Suggérer parmi... <span class="caret"></span></button>
										<ul class="dropdown-menu dropdown-custom">
											<?php while($user_label = $user_labels->fetch(PDO::FETCH_ASSOC)){ ?>
											<li class="completion-option"><a><?php echo $user_label["rank_name"];?></a></li>
											<?php } ?>
											<li class="completion-option"><a>Ne pas suggérer</a></li>
										</ul>
									</div>
									<input type="text" class="form-control filtered-complete" id="complete-teacher" name="session_teacher" value="<?php echo $cours['user_prenom']." ".$cours['user_nom'];?>">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="session_start" class="col-lg-3 control-label">Début</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="session_start" id="datepicker-start">
							</div>
						</div>
						<div class="form-group">
							<label for="session_end" class="col-lg-3 control-label">Fin</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="session_end" id="datepicker-end">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Etiquettes</label>
							<div class="col-lg-9">
								<h4>
									<?php while($label = $labels->fetch(PDO::FETCH_ASSOC)){
	if($label["is_mandatory"] == 1){
		$label_name = "<span class='glyphicon glyphicon-star'></span> ".$label["rank_name"];
	} else {
		$label_name = $label["rank_name"];
	}?>
									<span class="label label-salsabor label-clickable label-deletable" title="Supprimer l'étiquette" id="session-tag-<?php echo $label["entry_id"];?>" data-target="<?php echo $label["entry_id"];?>" data-targettype='session' style="background-color:<?php echo $label["tag_color"];?>"><?php echo $label_name;?></span>
									<?php } ?>
									<span class="label label-default label-clickable label-add trigger-sub" id="label-add" data-subtype='session-tags' data-targettype='session' title="Ajouter une étiquette">+</span>
								</h4>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Salle</label>
							<div class="col-lg-9">
								<select name="session_room" class="form-control">
									<?php while($salles = $querySalles->fetch(PDO::FETCH_ASSOC)){
	if($cours["session_room"] == $salles["room_id"]) {?>
									<option selected="selected" value="<?php echo $salles["room_id"];?>"><?php echo $salles["room_name"];?></option>
									<?php } else { ?>
									<option value="<?php echo $salles["room_id"];?>"><?php echo $salles["room_name"];?></option>
									<?php }
} ?>
								</select>
							</div>
						</div>
					</form>
					<p class="sub-legend top-divider">Groupe de récurrence</p>
					<form name="session_group" id="session_group" role="form" class="form-horizontal">
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Identifiant <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Groupe de récurrence auquel appartient le cours."></span></label>
							<div class="col-lg-9">
								<p type="text" class="form-control-static" name="cours_parent" id="group-input"><?php echo $cours["session_group"];?></p>
							</div>
						</div>
						<span class="col-lg-offset-2 col-lg-10 help-block">Modifiez les champs ci-dessous pour ajouter ou retirer des cours. Si vous prolongez la récurrence (en augmentant le nombre ou la date) de nouveaux cours seront créés. Inversement, si vous réduisez la récurrence, des cours existants seront supprimés.</span>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Nombre de cours</label>
							<div class="col-lg-9">
								<input type="number" class="form-control" id="steps" name="steps" value="<?php echo $count;?>">
							</div>
						</div>
						<div class="form-group">
							<label for="recurrence_end" class="col-lg-3 control-label">Fin de récurrence</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="recurrence_end" id="recurrence_end">
							</div>
						</div>
					</form>
					<div class="container-fluid">
						<button class="btn btn-primary col-xs-12 col-sm-offset-6 col-sm-6" id="group-edit">Valider les modifications d'appartenance</button>
						<!--<button class="btn btn-danger col-xs-6" id="group-split">Dissocier du groupe</button>-->
					</div>
					<p class="sub-legend top-divider">Participations de ce cours</p>
					<div class="panel panel-session" id="session-<?php echo $id;?>">
						<a class="panel-heading-container" id='ph-session-<?php echo $id;?>' data-session='<?php echo $id;?>' data-trigger='<?php echo $id;?>'>
							<div class="panel-heading">
								<div class="container-fluid">
									<p class="col-xs-5 col-md-3">Liste des participants</p>
									<p class="col-xs-2 col-lg-1"><span class="glyphicon glyphicon-user"></span> <span class="user-total-count" id="user-total-count-<?php echo $id;?>"></span></p>
									<p class="col-xs-2 col-lg-1"><span class="glyphicon glyphicon-ok"></span> <span class="user-ok-count" id="user-ok-count-<?php echo $id;?>"></span></p>
									<p class="col-xs-2 col-lg-1"><span class="glyphicon glyphicon-warning-sign"></span> <span class="user-warning-count" id="user-warning-count-<?php echo $id;?>"></span></p>
									<p class=" col-xs-1 col-md-1 col-md-offset-5 session-option"><span class="glyphicon glyphicon-ok-sign validate-session" id="validate-session-<?php echo $id;?>" data-session="<?php echo $id;?>" title="Valider tous les passages"></span></p>
								</div>
							</div>
						</a>
						<div class="panel-body collapse" id="body-session-<?php echo $id;?>" data-session="<?php echo $id;?>"></div>
					</div>
					<p class="sub-legend top-divider">Statistiques de participations du groupe de récurrence</p>
					<span class="help-block">Nombre de participants à chaque cours (Groupe de récurrence : <?php echo $cours["session_group"];?>)</span>
					<div class="chart" id="session-chart" style="height:250px"></div>
					<p class="sub-legend top-divider">Tâches à faire</p>
					<div class="tasks-container container-fluid"></div>
					<div class="sub-container container-fluid">
						<div class="panel-heading panel-add-record container-fluid">
							<div class="col-sm-1"><div class="notif-pp empty-pp"></div></div>
							<div class="col-sm-11 new-task-text">Ajouter une nouvelle tâche...</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$(document).ready(function(){
				$("#datepicker-start").datetimepicker({
					format: "DD/MM/YYYY HH:mm:00",
					defaultDate: "<?php echo date_create($cours['session_start'])->format("m/d/Y H:i");?>",
					locale: "fr",
					sideBySide: true,
					stepping: 30
				});
				$("#datepicker-end").datetimepicker({
					format: "DD/MM/YYYY HH:mm:00",
					defaultDate: "<?php echo date_create($cours['session_end'])->format("m/d/Y H:i");?>",
					locale: "fr",
					sideBySide: true,
					stepping: 30
				});
				window.openedSessions = [<?php echo $id;?>];
				initial_tags = [];
				$(".label-salsabor").each(function(){
					initial_tags.push($(this).text());
				})
				refreshTick();

				fetchTasks("SES", <?php echo $id;?>, 0, null, 0);

				var session_group_id = <?php echo $cours["session_group"];?>;
				window.initial_steps = $("#steps").val();

				$.get("functions/fetch_session_group.php", {group_id : session_group_id}).done(function(data){
					var group_details = JSON.parse(data);
					$("#recurrence_end").datetimepicker({
						format : "YYYY-MM-DD",
						locale: 'fr',
						defaultDate: group_details.parent_end_date
					}).on('dp.change', function(e){
						console.log("changed");
						if(!$("#steps").is(":focus")){
							var end_date = $(this).val();
							var starting_date = moment($("#datepicker-start").val()).format("YYYY-MM-DD");
							if(moment(end_date).isValid()){
								var delta = moment(moment(end_date).diff(starting_date));
								var delta_days = delta / (7 * 24 * 3600 * 1000);
								$("#steps").val(Math.trunc(delta_days));
							}
						}
					})
					$("#steps").keyup(function(){
						var steps = $(this).val();
						window.delta_steps = steps - initial_steps;
						var starting_date = moment(group_details.parent_end_date).format("YYYY-MM-DD");
						var end_date = moment(starting_date).add(delta_steps, 'w').format("YYYY-MM-DD")
						$("#recurrence_end").val(end_date);
						if(delta_steps < -1)
							$("#group-edit").text("Valider les modifications d'appartenance ("+-delta_steps+" cours retirés)");
						if(delta_steps == -1)
							$("#group-edit").text("Valider les modifications d'appartenance ("+-delta_steps+" cours retiré)");
						if(delta_steps == 0)
							$("#group-edit").text("Valider les modifications d'appartenance");
						if(delta_steps == 1)
							$("#group-edit").text("Valider les modifications d'appartenance ("+delta_steps+" cours ajouté)");
						if(delta_steps > 1)
							$("#group-edit").text("Valider les modifications d'appartenance ("+delta_steps+" cours ajoutés)");
					})
				})

				$.getJSON("functions/fetch_all_sessions_participations.php", {session_group_id : session_group_id}, function(data){
					new Morris.Line({
						// ID of the element in which to draw the chart.
						element: 'session-chart',
						// Chart data records -- each entry in this array corresponds to a point on
						// the chart.
						data: data,
						// The name of the data record attribute that contains x-values.
						xkey: 'date',
						// A list of names of data record attributes that contain y-values.
						ykeys: ['participations'],
						// Labels for the ykeys -- will be displayed when you hover over the
						// chart.
						labels: ['Participants'],
						lineColors: ['#A80139']
					});
				});
			}).on('click', '.btn-edit', function(){
				var id = $(this).attr("id");
				var form = $("#session_details"), entry_id = <?php echo $id;?>;
				switch(id){
					case "edit-one":
						var sessions = [entry_id];
						break;

					case "edit-next":
						var sessions = <?php echo $next_js;?>;
						break;

					case "edit-all":
						var sessions = <?php echo $all_js;?>;
						break;
				}
				var definitive_tags = [];
				$(".label-salsabor").each(function(){
					definitive_tags.push($(this).text());
				})
				$.post("functions/update_session.php", {sessions : sessions, values : form.serialize(), hook : entry_id}).done(function(data){
					// Attach & detach tags to other sessions
					for(var i = 0; i < sessions.length; i++){
						if(sessions[i] != entry_id){
							var copy_initial_tags = initial_tags;
							var copy_def_tags = definitive_tags;
							/* For each session, we have the tags when the page loaded in initial_tags. We'll now do something for each tag that exists NOW (from definitive_tags). 2 actions can be taken for the differences between the two arrays :
								-> The tag is not in the initial array but in the definitive one : it has to be attached to the sessions.
								-> The tag was in the initial array but not in the definitive one : it has to be detached from the sessions.
							*/
							/*console.log(copy_initial_tags);
							console.log(copy_def_tags);*/
							// WARNING : The code below, though very effective, is borderline intended by the developers of jQuery. If something breaks when uploading to a newer version of jQuery (> 2.1.4), please see here first.
							var to_be_detached = $(copy_initial_tags).not(copy_def_tags).get();
							var to_be_attached = $(copy_def_tags).not(copy_initial_tags).get();
							for(var j = 0; j < to_be_detached.length; j++){
								detachTag(to_be_detached[j], sessions[i], "session");
								console.log("detaching tag "+to_be_detached[j]+" from session "+sessions[i]);
							}
							for(var j = 0; j < to_be_attached.length; j++){
								attachTag(to_be_attached[j], sessions[i], "session");
								console.log("attaching tag "+to_be_attached[j]+" to session "+sessions[i]);
							}
						}
					}
					// We replace the original tags by the new ones after modifying.
					initial_tags = definitive_tags;
					// Close the well
					$(".in").collapse('hide');
					// Update the name of the session in the legend
					$(".session-name").text($("#session_name_input").val());
					// Update the last edition date
					$("#last-edit").text("Dernière modification le "+moment().format("DD/MM/YYYY [à] H:mm"));
				})
			}).on('click', '.btn-delete', function(){
				var id = $(this).attr("id"), entry_id = <?php echo $id;?>;
				var session_group_id = <?php echo $cours['session_group']?>;
				switch(id){
					case "delete-one":
						var sessions = [entry_id];
						break;

					case "delete-next":
						var sessions = <?php echo $next_js;?>;
						break;

					case "delete-all":
						var sessions = <?php echo $all_js;?>;
						break;
				}
				for(var i = 0; i < sessions.length; i++){
					if(i < sessions.length - 1){
						deleteEntry("cours", sessions[i]);
						deleteTasksByTarget("SES", sessions[i]);
					} else {
						console.log("checking parent");
						$.when(deleteEntry("cours", sessions[i]), deleteTasksByTarget("SES", sessions[i])).done(function(){
							$.get("functions/check_session_parent.php", {session_group_id : session_group_id}).done(function(data){
								window.top.location = "planning";
							})
						})
					}
				}
			}).on('click', '#group-edit', function(){
				var group_id = $("#group-input").text();
				$.post("functions/edit_group.php", {group_id : group_id, delta_steps : delta_steps}).done(function(data){
					console.log(data);
					$("#group-edit").text("Valider les modifications d'appartenance");
					if(delta_steps > 0){
						if($(".next-session").hasClass("disabled")){
							$(".next-session").replaceWith("<a href='cours/"+data+"' class='sub-legend next-session float-right'> Cours suivant <span class='glyphicon glyphicon-arrow-right'></span></a>");
						}
					}
					if(delta_steps < 0){
						var current_session_id = <?php echo $id;?>;
						if(data == current_session_id){
							$(".next-session").replaceWith("<p class='sub-legend next-session float-right disabled'> - <span class='glyphicon glyphicon-arrow-right'></span></p>");
						} else {
							if(moment($("#datepicker-end").val()).format("YYYY-MM-DD") > moment($("#recurrence_end").val()).format("YYYY-MM-DD")){
								console.log("cours/"+data);
								// If the displayed sesssion has been deleted too, we redirect the user to the last session of the group
								window.top.location = "cours/"+data;
							}
						}
					}
				});
			}).on('click', '.panel-add-record', function(){
				var emptyTask = "<div class='panel task-line task-new panel-new-task'>";
				emptyTask += "<div class='panel-heading container-fluid'>";
				emptyTask += "<div class='col-lg-1'>";
				emptyTask += "<div class='notif-pp'>";
				emptyTask += "<image src='' alt=''>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "<div class='col-sm-11'>";
				emptyTask += "<div class='row'>";
				emptyTask += "<p class='task-title col-sm-10'>";
				emptyTask += "<input class='form-control task-title-input' type='text' placeholder='Titre de la tâche'>";
				emptyTask += "</p>"
				emptyTask += "<div class='container-fluid'>";
				emptyTask += "<input class='form-control' id='task-target-input' type='hidden' value='[SES-<?php echo $id;?>]'>";
				emptyTask += "<textarea class='form-control task-description-input'></textarea>";
				emptyTask += "<button class='btn btn-primary post-task' id='post-task-button'>Valider</button>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				emptyTask += "</div>";
				$(".tasks-container").append(emptyTask);
				// When validating a new task, we delete the new template one and reload the correct one. Easy!
			})

			$('#paiement').change(function(){
				var state = $('#paiement').prop('checked');
				if(state){
					$('#paiement-sub').val(1);
				} else {
					$('#paiement-sub').val(0);
				}
			});
		</script>
	</body>
</html>
