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
$cours = $db->query("SELECT * FROM cours c
							JOIN users u ON c.prof_principal = u.user_id
							WHERE cours_id='$id'")->fetch(PDO::FETCH_ASSOC);

// Array of all the sessions from this parent.
$all = $db->query("SELECT cours_id FROM cours c WHERE cours_parent_id = $cours[cours_parent_id]")->fetchAll(PDO::FETCH_COLUMN);
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

$queryParent = $db->prepare("SELECT recurrence, frequence_repetition, parent_end_date FROM cours_parent WHERE parent_id=?");
$queryParent->bindParam(1, $cours['cours_parent_id'], PDO::PARAM_INT);
$queryParent->execute();
$res_recurrence = $queryParent->fetch(PDO::FETCH_ASSOC);

$querySalles = $db->query("SELECT * FROM rooms");

$labels = $db->query("SELECT * FROM assoc_session_tags us
						JOIN tags_session ts ON us.tag_id_foreign = ts.rank_id
						WHERE session_id_foreign = '$id'");

$user_labels = $db->query("SELECT * FROM tags_user");
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Cours de <?php echo $cours['cours_intitule'];?> (<?php echo date_create($cours['cours_start'])->format('d/m/Y');?> : <?php echo date_create($cours['cours_start'])->format('H:i')?> / <?php echo date_create($cours['cours_end'])->format('H:i');?>) | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script src="assets/js/participations.js"></script>
		<script src="assets/js/tags.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend>
						<span class="glyphicon glyphicon-eye-open"></span> <span class="session-name"><?php echo $cours['cours_intitule'];?></span>
						<div class="btn-toolbar float-right">
							<?php if($res_recurrence == '0'){ ?>
							<input type='submit' name='editOne' role='button' class='btn btn-success' value='ENREGISTRER'>
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
						<div class="col-sm-3">
							<?php if(isset($prev)){ ?>
							<a href="cours/<?php echo $prev;?>" class="sub-legend"><span class="glyphicon glyphicon-arrow-left"></span> Cours précédent</a>
							<?php } else { ?>
							<p class="sub-legend disabled"><span class="glyphicon glyphicon-arrow-left"></span> - </p>
							<?php } ?>
						</div>
						<div class="col-sm-6">
							<p id="last-edit"><?php if($cours['derniere_modification'] != '0000-00-00 00:00:00') echo "Dernière modification le ".date_create($cours['derniere_modification'])->format('d/m/Y')." à ".date_create($cours['derniere_modification'])->format('H:i');?></p>
						</div>
						<div class="col-sm-3">
							<?php if(isset($next)){ ?>
							<a href="cours/<?php echo $next;?>" class="sub-legend float-right">Cours suivant <span class="glyphicon glyphicon-arrow-right"></span></a>
							<?php } else { ?>
							<p class="sub-legend float-right disabled"> - <span class="glyphicon glyphicon-arrow-right"></span></p>
							<?php } ?>
						</div>
					</div>
					<form name="session_details" id="session_details" role="form" class="form-horizontal">
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Intitulé du cours</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="cours_intitule" id="session_name_input" value="<?php echo $cours['cours_intitule'];?>">
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
									<input type="text" class="form-control filtered-complete" id="complete-teacher" name="prof_principal" value="<?php echo $cours['user_prenom']." ".$cours['user_nom'];?>">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Début</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="cours_start" id="datepicker-start">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Fin</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="cours_end" id="datepicker-end">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Etiquettes</label>
							<div class="col-lg-9">
								<h4>
									<?php while($label = $labels->fetch(PDO::FETCH_ASSOC)){ ?>
									<span class="label label-salsabor label-clickable label-deletable" title="Supprimer l'étiquette" id="session-tag-<?php echo $label["entry_id"];?>" data-target="<?php echo $label["entry_id"];?>" data-targettype='session' style="background-color:<?php echo $label["tag_color"];?>"><?php echo $label["rank_name"];?></span>
									<?php } ?>
									<span class="label label-default label-clickable label-add trigger-sub" id="label-add" data-subtype='session-tags' data-targettype='session' title="Ajouter une étiquette">+</span>
								</h4>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-lg-3 control-label">Salle</label>
							<div class="col-lg-9">
								<select name="cours_salle" class="form-control">
									<?php while($salles = $querySalles->fetch(PDO::FETCH_ASSOC)){
	if($cours["cours_salle"] == $salles["room_id"]) {?>
									<option selected="selected" value="<?php echo $salles["room_id"];?>"><?php echo $salles["room_name"];?></option>
									<?php } else { ?>
									<option value="<?php echo $salles["room_id"];?>"><?php echo $salles["room_name"];?></option>
									<?php }
} ?>
								</select>
							</div>
						</div>
						<!--<div class="form-group">
<label for="" class="col-lg-3 control-label">Commentaires</label>
<div class="col-lg-9">
<textarea name="justification_modification" cols="30" rows="5" class="form-control"></textarea>
</div>
</div>-->
						<div class="panel panel-session" id="session-<?php echo $id;?>">
							<a class="panel-heading-container" id='ph-session-<?php echo $id;?>' data-session='<?php echo $id;?>' data-trigger='<?php echo $id;?>'>
								<div class="panel-heading">
									<div class="container-fluid">
										<p class="col-md-3">Liste des participants</p>
										<p class="col-lg-1"><span class="glyphicon glyphicon-user"></span> <span class="user-total-count" id="user-total-count-<?php echo $id;?>"></span></p>
										<p class="col-lg-1"><span class="glyphicon glyphicon-ok"></span> <span class="user-ok-count" id="user-ok-count-<?php echo $id;?>"></span></p>
										<p class="col-lg-1"><span class="glyphicon glyphicon-warning-sign"></span> <span class="user-warning-count" id="user-warning-count-<?php echo $id;?>"></span></p>
										<p class="col-md-1 col-md-offset-5 session-option"><span class="glyphicon glyphicon-ok-sign validate-session" id="validate-session-<?php echo $id;?>" data-session="<?php echo $id;?>" title="Valider tous les passages"></span></p>
									</div>
								</div>
							</a>
							<div class="panel-body collapse" id="body-session-<?php echo $id;?>" data-session="<?php echo $id;?>"></div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$(document).ready(function(){
				$("#datepicker-start").datetimepicker({
					format: "YYYY-MM-DD HH:mm:00",
					defaultDate: "<?php echo date_create($cours['cours_start'])->format("m/d/Y H:i");?>",
					locale: "fr",
					sideBySide: true,
					stepping: 30
				});
				$("#datepicker-end").datetimepicker({
					format: "YYYY-MM-DD HH:mm:00",
					defaultDate: "<?php echo date_create($cours['cours_end'])->format("m/d/Y H:i");?>",
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
				var parent_id = <?php echo $cours['cours_parent_id']?>;
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
					} else {
						console.log("checking parent");
						$.when(deleteEntry("cours", sessions[i])).done(function(){
							$.get("functions/check_session_parent.php", {parent_id : parent_id}).done(function(data){
								//console.log(data);
								window.top.location = "planning";
							})
						})
					}
				}
			}).on('click', '.completion-option', function(e){
				e.preventDefault();
				if($(this).text() == "Ne pas suggérer"){
					$(".suggestion-text").html("Suggérer parmi... <span class='caret'></span>");
				} else {
					$(".suggestion-text").html("Suggérer parmi <span class='suggestion-token'>"+$(this).text()+"</span> <span class='caret'></span>");
				}
			}).on('focus', '.filtered-complete', function(){
				var token = $(this).prev().find(".suggestion-token").text();
				if(token != ""){
					var id = $(this).attr("id");
					$.get("functions/fetch_user_list.php", {filter : token}).done(function(data){
						var userList = JSON.parse(data);
						var autocompleteList = [];
						for(var i = 0; i < userList.length; i++){
							autocompleteList.push(userList[i].user);
						}
						$("#"+id).textcomplete('destroy');
						$("#"+id).textcomplete([{
							match: /(^|\b)(\w{2,})$/,
							search: function(term, callback){
								callback($.map(autocompleteList, function(item){
									return item.toLowerCase().indexOf(term.toLocaleLowerCase()) === 0 ? item : null;
								}));
							},
							replace: function(item){
								return item;
							}
						}]);
					});
				}
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
