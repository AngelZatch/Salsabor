<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Jours Chômés | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-10 main">
					<h1 class="page-title"><span class="glyphicon glyphicon-leaf"></span> Jours Chômés</h1>
					<div class="btn-toolbar">
						<a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
						<button class="btn btn-info" id="add-holiday"><span class="glyphicon glyphicon-plus"></span> Ajouter un jour / une période chômé(e)</button>
					</div> <!-- btn-toolbar -->
					<div id="affected-details" class="collapse">
						<div id="affected-content" class="well">
							<div id="affected-list"></div>
							<a href="#affected-details" name="show-affected" class="btn btn-default" data-toggle="collapse">Fermer</a>
						</div>
					</div>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Jour chômé</th>
								<th></th>
							</tr>
						</thead>
						<tbody id="table-content">
							<tr id="new-holiday" style="display:none;">
								<td class="col-sm-3">Début de la période / jour unique<input type="date" class="form-control" id="date-debut">Fin de la période<input type="date" class="form-control" id="date-fin"></td>
								<td class="col-sm-6"><button class="btn btn-default" onclick="addHoliday()"><span class="glyphicon glyphicon-plus"></span> Valider</button><button class="btn btn-default" id="cancel"><span class="glyphicon glyphicon-minus"></span> Annuler</button></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$("#add-holiday").click(function(){
				$("#new-holiday").show();
			});

			$("#cancel").click(function(){
				$("#new-holiday").hide();
			});

			$(document).ready(function(){
				fetchHolidays();
			});

			function fetchHolidays(){
				$.post('functions/get_holiday.php').done(function(data){
					var json = JSON.parse(data);
					for (var i = 0; i < json.length; i++){
						var line = "<tr class='fetched' id ='holiday-"+json[i].id+"'>";
						line += "<td>";
						line += moment(json[i].date).lang('FR').format('ll');
						line += "</td><td>";
						line += "<button class='btn btn-default' onclick='deleteHoliday("+json[i].id+")'><span class='glyphicon glyphicon-trash'></span> Supprimer</button>";
						line += "</td></tr>";
						$("#table-content").append(line);
					}
				});
			}

			function addHoliday(){
				var start = $("#date-debut").val();
				var end = $("#date-fin").val();
				$.post('functions/add_holiday.php', {start, end}).done(function(data){
					var message = "Jour(s) chômé(s) ajouté(s).";
					showSuccessNotif(message);
					$(".fetched").remove();
					fetchHolidays();
					$("#affected-list").empty();
					var json = JSON.parse(data);
					var lineTop = "Vos modifications ont affecté les forfaits suivants : ";
					lineTop += "<ul>";
					$("#affected-list").append(lineTop);
					for (var i = 0; i < json.length; i++){
						var affectedLine = "<li>";
						affectedLine += json[i].id+" : "+json[i].old_date+" => "+json[i].new_date;
						affectedLine += "</li>";
						$("#affected-list").append(affectedLine);
					}
					var lineBottom = "</ul>";
					$("#affected-list").append(lineBottom);
					if(!$("#affected-details").hasClass("in")){
						$("*[name='show-affected']").click();
					}
				}).fail(function(data){
					console.log(data);
				});
				$("#new-holiday").hide();
			}

			function deleteHoliday(id){
				var delete_id = id;
				$.post('functions/delete_holiday.php', {delete_id}).done(function(data){
					var message = "Jour chômé supprimé.";
					showSuccessNotif(message);
					$(".fetched").remove();
					fetchHolidays();
					$("#affected-list").empty();
					var json = JSON.parse(data);
					var lineTop = "Vos modifications ont affecté les forfaits suivants : ";
					lineTop += "<ul>";
					$("#affected-list").append(lineTop);
					for (var i = 0; i < json.length; i++){
						var affectedLine = "<li>";
						affectedLine += json[i].id+" : "+json[i].old_date+" => "+json[i].new_date;
						affectedLine += "</li>";
						$("#affected-list").append(affectedLine);
					}
					var lineBottom = "</ul>";
					$("#affected-list").append(lineBottom);
					if(!$("#affected-details").hasClass("in")){
						$("*[name='show-affected']").click();
					}
				}).fail(function(data){
					console.log(data);
				});
			}
		</script>
	</body>
</html>
