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
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$(document).ready(function(){
				$.get("functions/fetch_rooms.php").done(function(data){
					var rooms = JSON.parse(data);
					var contents = "", previousLocation = -1;
					for(var i = 0; i < rooms.length; i++){
						console.log(i);
						if(rooms[i].location_id != previousLocation){
							if(i != 0){
								contents += constructNewPanel(previousLocation);
								// Close the row
								contents += "</div>";
							}
							contents += "<p class='sub-legend editable' id='location-name-"+rooms[i].location_id+"' data-input='text' data-table='locations' data-column='location_name' data-target='"+rooms[i].location_id+"' data-value='value'>"+rooms[i].location_name+"</p>";
							if(rooms[i].location_address == ""){
								var address = "Ajouter une adresse";
								var value = "no-value";
							} else {
								var address = rooms[i].location_address;
								var value = "value";
							}
							contents += "<p class='editable' id='location-address-"+rooms[i].location_id+"' data-input='text' data-table='locations' data-column='location_address' data-target='"+rooms[i].location_id+"' data-value='"+value+"'>"+address+"</p>";
							contents += "<div class='row'>";
						}
						if(rooms[i].room_id != null){
							contents += constructRoomPanel(rooms[i]);
						}
						previousLocation = rooms[i].location_id;
						if(i == rooms.length -1){
							contents += constructNewPanel(rooms[i].location_id);
							// Close the row
							contents += "</div>";
						}
					}
					contents += "<div class='panel-heading panel-add-record container-fluid'>";
					contents += "<div class='col-sm-1'><div class='notif-pp empty-pp'></div></div>";
					contents += "<div class='col-sm-11 new-task-text'>Ajouter un nouveau lieu</div>";
					contents += "</div></div>";
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
			}).on('click', '.panel-add-record', function(){
				$(this).before("<input type='text' class='form-control' id='new-location'>");
				$("#new-location").focus();
			}).on('blur', '#new-location', function(){
				var name = $("#new-location").val();
				if(name != ""){
					$.post("functions/add_location.php", {location_name : name}).done(function(data){
						var new_location = "<p class='sub-legend editable' id='location-name-"+data+"' data-input='text' data-table='locations' data-column='location_name' data-target='"+data+"' data-value='value'>"+name+"</p>";
						new_location += "<p class='editable' id='location-address-"+data+"' data-input='text' data-table='locations' data-column='location_address' data-target='"+data+"' data-value='no-value'>Ajouter une adresse</p>";
						new_location += "<div class='row'>";
						new_location += constructNewPanel(data);
						new_location += "</div>";
						$("#new-location").replaceWith(new_location);
					})
				} else {
					$("#new-location").remove();
				}
			}).on('click', '.color-cube', function(e){
				// Assign a color to a room
				e.stopPropagation();
				var cube = $(this);
				var target = document.getElementById(cube.attr("id")).dataset.target;
				var color_id = document.getElementById(cube.attr("id")).dataset.color;
				var value = /([a-z0-9]+)/i.exec(cube.css("backgroundColor"));
				var table = "rooms";
				$.when(updateColumn(table, "room_color", color_id, target)).done(function(data){
					$("#room-color-cube-"+target).css("background-color", "#"+value[0]);
					$(".color-cube").empty();
					cube.append("<span class='glyphicon glyphicon-ok color-selected'></span>");
				})
			})

			function constructRoomPanel(room){
				var contents = "";
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
				contents += "<div class='col-xs-1 room-cube trigger-sub' id='room-color-cube-"+room.room_id+"' data-subtype='room-color' data-target='"+room.room_id+"' style='background-color:#"+room.room_color+"' title='Couleur de la salle. Cliquez pour changer la couleur'></div>";
				contents += "<p class='col-xs-8 editable' id='room-name-"+room.room_id+"' data-input='text' data-table='rooms' data-column='room_name' data-target='"+room.room_id+"' data-value='value'>"+room.room_name+"</p>";
				contents += "<p class='col-xs-2'><span class='glyphicon glyphicon-trash "+trash_class+"' id='delete-"+room.room_id+"' data-target='"+room.room_id+"' title='"+trash_title+"'></span></p>";
				contents += "</div>"; // panel-title
				contents += "<div class='container-fluid'>";
				contents += "<span class='glyphicon glyphicon-star col-xs-2'></span> ";
				contents += "<p class='col-xs-10 purchase-sub no-padding'>"+status+"</p>";
				contents += "</div>"; // container-fluid
				if(room.reader_token != null){
					var reader = room.reader_token;
					var value = "value";
				} else {
					var reader = "Pas de lecteur couplé";
					var value = "no-value";
				}
				contents += "<div class='container-fluid'>";
				contents += "<span class='glyphicon glyphicon-hdd col-xs-2'></span> <p class='editable col-xs-10 no-padding' id='room-reader-"+room.room_id+"' data-input='text' data-table='rooms' data-column='room_reader' data-target='"+room.room_id+"' data-value='"+value+"'>"+reader+"</p>";
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
				contents += "<p class='col-xs-12'>Ajouter une salle à ce lieu</p>";
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
