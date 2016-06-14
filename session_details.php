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

// Sauf d'un seul cours
if(isset($_POST['deleteCoursOne'])){
	deleteCoursOne();
}

// Suppression de tous les cours suivant le sélectionné
if(isset($_POST['deleteCoursNext'])){
	deleteCoursNext();
}

// Suppression de tous les cours du même genre que le sélectionné
if(isset($_POST['deleteCoursAll'])){
	deleteCoursAll();
}
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
							<input type="submit" name="deleteCoursOne" role="button" class="btn btn-danger" value="Cet évènement">
							<input type="submit" name="deleteCoursNext" role="button" class="btn btn-danger" value="Tous les suivants">
							<input type="submit" name="deleteCoursAll" role="button" class="btn btn-danger" value="Toute la série">
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
							<label for="" class="col-lg-3 control-label">Professeur</label>
							<div class="col-lg-9">
								<input type="text" class="form-control" name="prof_principal" value="<?php echo $cours['user_prenom']." ".$cours['user_nom'];?>">
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
									<span class="label label-salsabor label-clickable label-deletable" title="Supprimer l'étiquette" id="user-tag-<?php echo $label["entry_id"];?>" data-target="<?php echo $label["entry_id"];?>" data-targettype='user' style="background-color:<?php echo $label["tag_color"];?>"><?php echo $label["rank_name"];?></span>
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
				console.log(sessions);
				$.post("functions/update_session.php", {sessions : sessions, values : form.serialize(), hook : entry_id}).done(function(data){
					console.log(data);
					// Close the well
					$(".in").collapse('hide');
					// Update the name of the session in the legend
					$(".session-name").text($("#session_name_input").val());
					// Update the last edition date
					$("#last-edit").text("Dernière modification le "+moment().format("DD/MM/YYYY [à] H:mm"));
				})
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
