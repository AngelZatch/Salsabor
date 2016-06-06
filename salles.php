<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Salles | Salsabor</title>
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/circle-progress.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-pushpin"></span> Salles
					</legend>
					<div id="rooms-list" class="container-fluid">
					</div>
				</div>
			</div>
		</div>
		<script>
			$(document).ready(function(){
				$.get("functions/fetch_rooms.php").done(function(data){
					var rooms = JSON.parse(data);
					var contents = "", previousLocation = -1;
					for(var i = 0; i < rooms.length; i++){
						if(rooms[i].location_id != previousLocation){
							if(i != 0){
								contents += constructNewPanel(previousLocation);
								// Close the row
								contents += "</div>";
							}
							contents += "<p class='sub-legend'>"+rooms[i].location_name+"</p>";
							contents += "<div class='row'>";
						}
						contents += constructRoomPanel(rooms[i]);
						previousLocation = rooms[i].location_id;
						if(i == rooms.length -1){
							contents += constructNewPanel();
							// Close the row
							contents += "</div>";
						}
					}
					$("#rooms-list").append(contents);
				})
			}).on('mousedown', '.glyphicon-trash', function(){
				if($(this).hasClass("glyphicon-button")){
					var target = document.getElementById($(this).attr("id")).dataset.target;
					var toBeDeleted = $("#dah-"+target);
					$("#dah-"+target).show();
					var startAngle = -Math.PI/2;
					toBeDeleted.circleProgress({
						value: 1,
						size: 60,
						startAngle: startAngle,
						thickness: 100/18,
						lineCap: "round",
						fill:{
							color: "white"
						},
						animation: {
							duration: 2500
						}
					}).on('circle-animation-end', function(e){
						var value = toBeDeleted.data('circle-progress').lastFrameValue;
						if(value == 1){
							// Deletion code
							$.when(deleteEntry("rooms", target)).done(function(data){
								console.log(data);
								$("#room-"+target).remove();
							})
						}
					})
				}
			}).on('mouseup', '.delete-animation-holder', function(){
				var target = document.getElementById($(this).attr("id")).dataset.target;
				var toBeDeleted = $("#dah-"+target);
				$("#dah-"+target).hide();
				$(toBeDeleted.circleProgress('widget')).stop();
			}).on('click', '.status-new', function(){
				var parent = $(this).parent();
				var location = document.getElementById($(this).attr("id")).dataset.location;
				parent.before(constructEmptyPanel(location));
				$("#new-name").focus();
			}).on('click', '.create-room', function(){
				var location = document.getElementById($(this).attr("id")).dataset.location;
				var name = $("#new-name").val();
				$.post("functions/add_room.php", {room_location : location, room_name : name}).done(function(data){
					var new_room = {room_id : data, room_name : name};
					$(".status-pre-success").parent().replaceWith(constructRoomPanel(new_room));
				})
			}).on('blur', '#new-name', function(){
				if($(this).val() == ""){
					$(".status-pre-success").parent().remove();
				}
			})

			function constructRoomPanel(room){
				var contents = "";
				console.log(room);
				contents += "<div class='col-xs-12 col-md-6 col-lg-4' id='room-"+room.room_id+"'>";
				if(room.availability == 0){
					var availability_class = "status-over";
					var status = room.current_session+" (jusqu'à "+moment(room.current_end).format("HH:mm")+")";
					var trash_class = "glyphicon-button-disabled not-allowed";
					var trash_title = "Vous ne pouvez pas supprimer une salle occupée";
				} else if(room.availability == 0.5){
					var availability_class = "status-partial-success";
					var status = room.next_session+" (à partir de "+moment(room.next_start).format("HH:mm")+")";
					var trash_class = "glyphicon-button-disabled not-allowed";
					var trash_title = "Vous ne pouvez pas supprimer une salle occupée";
				} else {
					var availability_class = "status-success";
					var status = "Disponible";
					var trash_class = "glyphicon-button";
					var trash_title = "Supprimer la salle (Maintenez appuyé)";
				}
				contents += "<div class='panel panel-item panel-room "+availability_class+"'>";
				contents += "<div class='panel-body row'>";
				contents += "<div class='delete-animation-holder' id='dah-"+room.room_id+"' data-target='"+room.room_id+"'><p class='hold-text'>Suppression...</p><p class='hold-help'>(Relâchez pour annuler)</p></div>";
				contents += "<div class='panel-title container-fluid'>";
				contents += "<p class='col-xs-10'>"+room.room_name+"</p>";
				contents += "<p class='col-xs-2'><span class='glyphicon glyphicon-trash "+trash_class+"' id='delete-"+room.room_id+"' data-target='"+room.room_id+"' title='"+trash_title+"'></span></p>";
				contents += "</div>"; // panel-title
				contents += "<div class='container-fluid'>";
				contents += "<span class='glyphicon glyphicon-star col-xs-2'></span> ";
				contents += "<p class='col-xs-10 purchase-sub'>"+status+"</p>";
				contents += "</div>"; // container-fluid
				if(room.reader_token != null){
					var reader = room.reader_token;
					var value = "value";
				} else {
					var reader = "Pas de lecteur couplé";
					var value = "no-value";
				}
				contents += "<div class='container-fluid'>";
				contents += "<span class='glyphicon glyphicon-hdd glyphicon-description col-xs-2'></span> <p class='editable' id='room-reader-"+room.room_id+"' data-input='text' data-table='rooms' data-column='room_reader' data-target='"+room.room_id+"' data-value='"+value+"'>"+reader+"</p>";
				contents += "</div>"; //container-fluid
				contents += "</div>"; //panel-body
				contents += "</div>"; //panel
				contents += "</div>"; //col-xs-12 col-md-6 col-lg-4
				return contents;
			}

			function constructNewPanel(location){
				var contents = "";
				contents += "<div class='col-xs-12 col-md-6 col-lg-4'>";
				contents += "<div class='panel panel-item panel-room status-new' id='new-"+location+"' data-location='"+location+"'>";
				contents += "<div class='panel-body'>";
				// Panel-title
				contents += "<div class='panel-title'>";
				contents += "<p class='col-xs-12'>Ajouter une salle à cette location</p>";
				contents += "</div>";
				contents += "</div>";
				contents += "</div>";
				contents += "</div>";
				return contents;
			}

			function constructEmptyPanel(location){
				var contents = "";
				contents += "<div class='col-xs-12 col-md-6 col-lg-4'>";
				contents += "<div class='panel panel-item panel-room status-pre-success'>";
				contents += "<div class='panel-body'>";
				contents += "<p class='panel-title'><input type='text' class='form-control' id='new-name'></p>";
				contents += "<p><span class='glyphicon glyphicon-hdd'></span> - </p>";
				contents += "<button class='btn btn-success btn-block create-room' id='create-"+location+"' data-location='"+location+"'>Ajouter</button>";
				contents += "</div>";
				contents += "</div>";
				contents += "</div>";
				return contents;
			}
		</script>
	</body>
</html>
