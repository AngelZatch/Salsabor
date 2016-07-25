<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
/** Le fichier functions/cours.php contient toutes les fonctions relatives aux cours **/
require_once "functions/cours.php";
require_once "functions/reservations.php";

$rooms = $db->query("SELECT room_id, room_name, color_value FROM rooms r
					JOIN colors c ON r.room_color = c.color_id")->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Planning | Salsabor</title>
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/participations.js"></script>
		<script src="assets/js/check_calendar.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-time"></span> Planning
						<a href="cours_add.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Ajouter un cours</a>
					</legend>
					<span class="help-block">Sur périphériques tactiles, maintenez appuyé pour sélectionner un événement ou une plage horaire.</span>
					<div class="filters row">
						<div class="container-fluid col-xs-12 col-sm-4 col-lg-3">
							<p class="filter-title" data-toggle="collapse" href="#room-filtering" title="Cliquez pour dérouler les salles disponibles">Vos salles <span class="glyphicon glyphicon-menu-down float-right"></span></p>
							<ul class="collapse" id="room-filtering">
								<?php foreach($rooms as $room){ ?>
								<div class="room-filter" id="room-<?php echo $room["room_id"];?>" data-room="<?php echo $room["room_id"];?>" data-filter="1" title="Cliquez pour activer ou désactiver l&apos;affichage d&apos;une salle dans le planning">
									<div class="cube-filter enabled" style="background-color: #<?php echo $room["color_value"];?>"></div>
									<p class="filter-name"><?php echo $room["room_name"];?></p>
								</div>
								<?php } ?>
							</ul>
						</div>
					</div>
					<div id="display-planning" style="display:block;">
						<div id="calendar" class="fc fc-ltr fc-unthemed"></div>
					</div> <!-- Display en Planning -->
				</div> <!-- col-sm-offset-3 col-lg-10 col-lg-offset-2 main -->
				<?php include "inserts/sub_modal_session.php";?>
				<div id="reservation-options" class="popover popover-default">
					<div class="arrow"></div>
					<p style="font-weight:700;" id="popover-reservation-title"></p>
					<p id="popover-reservation-type"></p>
					<p id="popover-reservation-hours"></p>
					<a class="btn btn-default col-sm-12"><span class="glyphicon glyphicon-edit"></span> Détails >></a>
				</div>
			</div>
		</div>
		<style>
			.sub-modal{
				z-index: 3;
			}
			.sub-modal-header{
				border: none;
			}
			.sub-modal-body{
				overflow: visible;
			}
			.sub-modal-title{
				font-weight: 700;
				margin: 0;
				font-size: 18px;
			}
			.session-modal-details>span{
				color: #CCC;
				margin-right: 20px;
			}
		</style>
		<script>
			$(document).ready(function ($) {
				// delegate calls to data-toggle="lightbox"
				$(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
					event.preventDefault();
					return $(this).ekkoLightbox({
						onNavigate: false
					});
				});

				var docHeight = $(document).height();
				var xPos = $("#calendar").position();
				var height = docHeight - xPos.top - 100;
				if(height < 350)
					height = docHeight - xPos.top + 40;

				// Full calendar
				$('#calendar').fullCalendar({
					contentHeight: height,
					defaultView: 'agendaWeek',
					endParam: 'fetch_end',
					editable: false,
					eventOrder: "lieu",
					eventSources:[
						{
							url: 'functions/calendarfeed_cours.php',
							type: 'GET',
							data: function(){
								var filters = [];
								$(".room-filter").each(function(){
									if(document.getElementById($(this).attr("id")).dataset.filter == 1){
										filters.push(document.getElementById($(this).attr("id")).dataset.room);
									}
								})
								return {
									filters: filters
								};
							},
							color: '#0FC5F5',
							textColor:'black',
							error: function(data){
								console.log(data);
							}
						},
						{
							url: 'functions/calendarfeed_resa.php',
							type: 'GET',
							color: '#D21CFC',
							textColor: 'black',
							error: function(){
								console.log('Erreur pendant l\'obtention des réservations');
							}
						},
						{
							url: 'functions/calendarfeed_holidays.php',
							type: 'GET',
							color: '#C4C4C4',
							textColor: 'black',
							rendering: 'background',
							error: function(data){
								console.log(data);
							}
						}
					],
					eventRender: function(calEvent, element){
						if(calEvent.type == "cours"){
							element.attr('id', calEvent.type+'-'+calEvent.id);
							element.attr('salle', calEvent.lieu);
							element.css("background-color", "#"+calEvent.color);
						}
						if(calEvent.type == "holiday") {
							element.css('background-color', '#000');
						}
						if(calEvent.type == 'reservation'){
							if (calEvent.priorite == 0){
								element.css('background-color', '#ebb3f9');
								element.css('font-style', 'italic');
								element.css('color', '#555');
								element.css('border', 'dashed 2px');
							} else {
								element.css('background-color', '#D21CFC');
							}
						}
					},
					eventClick: function(calEvent, jsEvent, element){
						var target = $(this).attr("id").match(/\d+/);
						if(target == $("#sub-modal-session").data().target){
							$(".sub-modal-session").hide();
							$("#sub-modal-session").data().target = -1;
						} else {
							$("#sub-modal-session").data().target = target[0];
							$.get("functions/fetch_session_details.php", {session_id : target[0]}).done(function(data){
								var session = JSON.parse(data);
								// Emptying fields
								$(".sub-modal-title").empty();
								$(".session-date").empty();
								$(".session-room").empty();
								$(".session-participations").empty();
								$(".sub-modal-footer").empty();
								// Color change
								$(".sub-modal-title").css("color", session.color);
								// Filling fields
								$(".sub-modal-title").append("<span class='glyphicon glyphicon-eye-open'></span> "+session.title);
								$(".session-date").append("<span>Date</span>"+moment(session.start).format("ll[,] HH:mm")+" - "+moment(session.end).format("HH:mm"));
								$(".session-room").append("<span>Lieu</span>"+session.room);
								$(".session-participations").append("<span>Participants</span>"+session.participations_count);
								$(".sub-modal-footer").append("<a href='cours/"+target+"' class='btn btn-default float-right btn-to-session'><span class='glyphicon glyphicon-search'></span> Détails...</a>");
								// Showing modal once everything is done
								$(".sub-modal-session").show();
							})
							var top = jsEvent.pageY;
							var left = jsEvent.pageX;
							var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
							var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
							var modal_w = $(".sub-modal-session").width();
							var modal_h = $(".sub-modal-session").height();
							if(top > h - modal_h){
								top -= (modal_h + 20);
							}
							if(left > w - modal_w){
								left -= (modal_w + 20);
							}
							console.log(top, left);
							$(".sub-modal-session").css({
								top : top+'px',
								left : left+'px'
							})
						}
					},
					header:{
						left:'prev,next today',
						center:'title',
						right:'month, agendaWeek, agendaDay'
					},
					hiddenDays: [0],
					lang:'fr',
					minTime: '9:00',
					nowIndicator: true,
					select: function(start, end, jsEvent, view){
						jsEvent.stopImmediatePropagation();
						$(".sub-modal-session").hide();
						$("#sub-modal-session").data().target = -1;

						$(".sub-modal-title").empty();
						$(".session-date").empty();
						$(".session-room").empty();
						$(".session-participations").empty();
						$(".sub-modal-footer").empty();
						// Color change
						$(".sub-modal-title").css("color", "000000");

						// We get the duration selected by the user
						var selected_duration = end.diff(start);

						// We check to see if the day selected is already a holiday or not
						$.when(isHoliday(moment(start).format("YYYY-MM-DD"))).done(function(holiday_check_value){
							// Filling fields depending on the duration
							if(selected_duration == 86400000){ // The user has selected a full day
								$(".sub-modal-title").append("<span class='glyphicon glyphicon-calendar'></span> Jour entier");
								$(".session-date").append("<span>Date</span>"+moment(start).format("ll"));
								if(holiday_check_value == -1){
									$(".sub-modal-footer").append("<button class='btn btn-default btn-to-session' id='quick-add-holiday' data-date='"+moment(start).format("YYYY-MM-DD")+"' data-duration='1'><span class='glyphicon glyphicon-leaf'></span> Ajouter un jour chômé</a>");
								} else {
									$(".sub-modal-footer").append("<button class='btn btn-default btn-to-session' id='quick-remove-holiday' data-date='"+moment(start).format("YYYY-MM-DD")+"' data-duration='1'><span class='glyphicon glyphicon-leaf'></span> Retirer le jour chômé</a>");
								}
							} else if(selected_duration < 8640000) { // The user has selected a duration shorter than a day
								$(".sub-modal-title").append("<span class='glyphicon glyphicon-eye-open'></span> Ajouter un cours");
								$(".session-date").append("<span>Date</span>"+moment(start).format("ll[,] HH:mm")+" - "+moment(end).format("HH:mm"));
								if(holiday_check_value == -1){
									$(".sub-modal-footer").append("<button class='btn btn-default btn-to-session' id='quick-add-holiday' data-date='"+moment(start).format("YYYY-MM-DD")+"' data-duration='1'><span class='glyphicon glyphicon-leaf'></span> Ajouter un jour chômé</a>");
								} else {
									$(".sub-modal-footer").append("<button class='btn btn-default btn-to-session' id='quick-remove-holiday' data-date='"+moment(start).format("YYYY-MM-DD")+"' data-duration='1'><span class='glyphicon glyphicon-leaf'></span> Retirer le jour chômé</a>");
								}
							} else {
								// For a duration longer than a day, we can still add holidays, with the duration data
								$(".sub-modal-title").append("<span class='glyphicon glyphicon-eye-open'></span> Ajouter un événement");
								// To have a better display, we can check if the user has selected only full days by checking the remainder after a modulo operation. If the remainder is 0, it means the user has selected 2 or more full days, so we can skip on displaying hours.
								if(selected_duration % 86400000 == 0){
									$(".session-date").append("<span>Date</span>"+moment(start).format("ll")+" - "+moment(end).format("ll"));
								} else {
									$(".session-date").append("<span>Date</span>"+moment(start).format("ll[,] HH:mm")+" - "+moment(end).format("ll[,] HH:mm"));
								}
								var duration = moment(end).diff(moment(start)) / (3600 * 24 * 1000);
								if(holiday_check_value == -1){
									$(".sub-modal-footer").append("<button class='btn btn-default btn-to-session' id='quick-add-holiday' data-date='"+moment(start).format("YYYY-MM-DD")+"' data-duration='"+duration+"'><span class='glyphicon glyphicon-leaf'></span> Ajouter une période chômée</a>");
								} else {
									$(".sub-modal-footer").append("<button class='btn btn-default btn-to-session' id='quick-remove-holiday' data-date='"+moment(start).format("YYYY-MM-DD")+"' data-duration='"+duration+"'><span class='glyphicon glyphicon-leaf'></span> Retirer la période chômée</a>");
								}
							}
							$(".sub-modal-footer").append("<a href='cours_add.php' class='btn btn-primary float-right btn-to-session'>Ajouter</a>");
						})

						var top = jsEvent.pageY;
						var left = jsEvent.pageX;
						var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
						var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
						var modal_w = $(".sub-modal-session").width();
						var modal_h = $(".sub-modal-session").height();
						if(top > h - modal_h){
							top -= (modal_h + 20);
						}
						if(left > w - modal_w){
							left -= (modal_w + 20);
						}
						$(".sub-modal-session").css({
							top : top+'px',
							left : left+'px'
						})

						sessionStorage.removeItem('end');
						sessionStorage.removeItem('start');
						sessionStorage.setItem('start', start);
						sessionStorage.setItem('end', end);
						// Showing modal once everything is done
						$(".sub-modal-session").show();
					},
					selectable: true,
					selectHelper: true,
					slotEventOverlap: false,
					snapDuration: "01:00",
					startParam: 'fetch_start',
					timeFormat: 'H:mm',
					timezone: 'local',
					unselect: function(){
						$(".sub-modal-session").hide();
					},
					unselectCancel: '.btn-to-session',
					viewRender: function(){
						$("#calendar").fullCalendar('refetchEvents');
					}
				});
			}).on('click', '#quick-add-holiday', function(){
				var date = document.getElementById($(this).attr("id")).dataset.date;
				var duration = document.getElementById($(this).attr("id")).dataset.duration;
				$.when(postOrDeleteHolidays(date, duration, "post")).done(function(data){
					console.log(data);
					$(".sub-modal-session").hide();
					$("#calendar").fullCalendar('refetchEvents');
				})
			}).on('click', '#quick-remove-holiday', function(){
				var date = document.getElementById($(this).attr("id")).dataset.date;
				var duration = document.getElementById($(this).attr("id")).dataset.duration;
				$.when(postOrDeleteHolidays(date, duration, "delete")).done(function(data){
					$(".sub-modal-session").hide();
					$("#calendar").fullCalendar('refetchEvents');
				})
			}).on('click', '.room-filter', function(){
				var id = $(this).attr("id");
				if($(this).children(".cube-filter").hasClass("enabled")){
					document.getElementById(id).dataset.filter = 0;
					$(this).children(".cube-filter").switchClass("enabled", "disabled");
				} else {
					document.getElementById(id).dataset.filter = 1;
					$(this).children(".cube-filter").switchClass("disabled", "enabled");
				}
				$("#calendar").fullCalendar('refetchEvents');
			})

			function postOrDeleteHolidays(date, duration, postOrDelete){
				return $.post("functions/post_or_delete_holidays.php", {holiday_date : date, duration : duration, action : postOrDelete});
			}

			function isHoliday(date){
				return $.get("functions/check_holidays.php", {check_date : date});
			}
		</script>
	</body>
</html>
