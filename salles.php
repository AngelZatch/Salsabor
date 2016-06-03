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
					console.log(rooms);
					var contents = "", previousLocation = -1;
					for(var i = 0; i < rooms.length; i++){
						if(rooms[i].location_id != previousLocation){
							if(i != 0){
								contents += constructNewPanel();
								// Close the row
								contents += "</div>";
							}
							contents += "<p class='sub-legend'>"+rooms[i].location_name+"</p>";
							contents += "<div class='row'>";
						}
						contents += "<div class='col-xs-12 col-md-6 col-lg-4' id='room-"+rooms[i].room_id+"'>";
						if(rooms[i].availability == 1){
							var availability_class = "status-over";
							var status = "Actuellement occupée";
							var trash_class = "glyphicon-button-disabled not-allowed";
							var trash_title = "Vous ne pouvez pas supprimer une salle occupée";
						} else {
							var availability_class = "status-success";
							var status = "Disponible";
							var trash_class = "glyphicon-button";
							var trash_title = "Supprimer la salle (Maintenez appuyé)";
						}
						contents += "<div class='panel panel-item panel-room "+availability_class+"'>";
						contents += "<div class='panel-body'>";
						contents += "<div class='delete-animation-holder' id='dah-"+rooms[i].room_id+"' data-target='"+rooms[i].room_id+"'><p class='hold-text'>Suppression...</p><p class='hold-help'>(Relâchez pour annuler)</p></div>";
						//contents += "<div>";
						contents += "<p class='panel-title col-xs-11'>"+rooms[i].room_name+"</p>";
						contents += "<p class='panel-title col-xs-1'><span class='glyphicon glyphicon-trash "+trash_class+"' id='delete-"+rooms[i].room_id+"' data-target='"+rooms[i].room_id+"' title='"+trash_title+"'></span></p>";
						//contents += "</div>";
						contents += "<p class='purchase-sub col-xs-12'><span class='glyphicon glyphicon-star'></span> "+status+"</p>";
						if(rooms[i].reader_token != null){
							var reader = rooms[i].reader_token;
						} else {
							var reader = "Pas de lecteur couplé";
						}
						contents += "<p class='col-xs-12'><span class='glyphicon glyphicon-hdd'></span> "+reader+"</p>";
						contents += "</div>";
						contents += "</div>";
						contents += "</div>";
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
			})

			function constructNewPanel(){
				var contents = "";
				contents += "<div class='col-xs-12 col-md-6 col-lg-4'>";
				contents += "<div class='panel panel-item status-new'>";
				contents += "<div class='panel-body'>";
				contents += "<p class='panel-title'>Ajouter une salle à cette location</p>";
				contents += "<p><span class='glyphicon glyphicon-star'></span> - </p>";
				contents += "<p><span class='glyphicon glyphicon-hdd'></span> - </p>";
				contents += "</div>";
				contents += "</div>";
				contents += "</div>";
				return contents;
			}
		</script>
	</body>
</html>
