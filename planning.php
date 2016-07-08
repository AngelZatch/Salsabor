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
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Planning | Salsabor</title>
		<?php include "styles.php";?>
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
					<div id="display-planning" style="display:block;">
						<div id="calendar" class="fc fc-ltr fc-unthemed"></div>
					</div> <!-- Display en Planning -->
				</div> <!-- col-sm-offset-3 col-lg-10 col-lg-offset-2 main -->
				<?php include "inserts/sub_modal_session.php";?>
				<div id="cours-options" class="popover popover-default">
					<div class="arrow"></div>
					<p style="font-weight:700;" id="popover-cours-title"></p>
					<p id="popover-cours-type"></p>
					<p id="popover-cours-hours"></p>
					<a role="button" class="btn btn-default col-sm-12"><span class="glyphicon glyphicon-edit"></span> Détails >></a>
				</div>
				<div id="reservation-options" class="popover popover-default">
					<div class="arrow"></div>
					<p style="font-weight:700;" id="popover-reservation-title"></p>
					<p id="popover-reservation-type"></p>
					<p id="popover-reservation-hours"></p>
					<a class="btn btn-default col-sm-12"><span class="glyphicon glyphicon-edit"></span> Détails >></a>
				</div>
			</div>
			<div id="add-options" class="popover popover-default">
				<div class="arrow"></div>
				<p style="font-weight:700;" id="popover-new-title">Ajouter</p>
				<p id="popover-new-hours"></p>
				<div class="btn-group col-sm-12" role="group">
					<a class="btn btn-default" style="background-color:#0FC5F5;" href="cours_add.php?"><span class="glyphicon glyphicon-plus"></span> Cours</a>
					<a class="btn btn-default" style="background-color:#ebb3f9;" href="resa_add.php"><span class="glyphicon glyphicon-record"></span> Location</a>
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
		<?php include "scripts.php";?>
		<script src="assets/js/participations.js"></script>
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

				// Full calendar
				$('#calendar').fullCalendar({
					header:{
						left:'prev,next today',
						center:'title',
						right:'month, agendaWeek, agendaDay'
					},
					defaultView: 'agendaWeek',
					lang:'fr',
					timezone: 'local',
					editable: false,
					selectable: true,
					selectHelper: true,
					hiddenDays: [0],
					minTime: '6:00',
					timeFormat: 'H:mm',
					nowIndicator: true,
					allDaySlot: false,
					handleWindowResize: true,
					contentHeight: height,
					eventSources:[
						{
							url: 'functions/calendarfeed_cours.php',
							type: 'POST',
							color: '#0FC5F5',
							textColor:'black',
							error: function(){
								console.log('Erreur pendant l\'obtention des évènements');
							}
						},
						{
							url: 'functions/calendarfeed_resa.php',
							type: 'POST',
							color: '#D21CFC',
							textColor: 'black',
							error: function(){
								console.log('Erreur pendant l\'obtention des réservations');
							},
						},
						{
							url: 'functions/calendarfeed_holidays.php',
							type: 'POST',
							color: '#C4C4C4',
							textColor: 'black',
							rendering: 'background',
							error: function(){
								console.log('Erreur pendant l\'obtention des jours chômés');
							},
						}
					],
					eventRender: function(calEvent, element){
						element.attr('id', calEvent.type+'-'+calEvent.id);
						element.attr('salle', calEvent.lieu);
						if(calEvent.type == 'reservation'){
							if (calEvent.priorite == 0){
								element.css('background-color', '#ebb3f9');
								element.css('font-style', 'italic');
								element.css('color', '#555');
								element.css('border', 'dashed 2px');
							} else {
								element.css('background-color', '#D21CFC');
							}
						}  else if(calEvent.type == 'holiday'){
							element.css('background-color', '#000');
						} else {
							element.css("background-color", "#"+calEvent.color);/*
							switch(calEvent.prestation_id){
								case '6':
								case '7':
								case '8':
									element.css('background-color', '#2DF588');
									element.css('border-color', '#28DB7A');
									break;

								default:
									break;
							}*/
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
								$(".sub-modal-footer").append("<a href='cours/"+target+"' class='btn btn-primary float-right btn-to-session'>Modifier</a>");
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
					select: function(start, end, jsEvent, view){
						var options = {
							target: "#undefined-undefined",
							placement: 'top',
							closeOtherPopovers: true,
						};
						var position = $("#undefined-undefined").offset();
						var bWidth = $("#undefined-undefined").width();
						$('#add-options').popoverX(options);
						$('#add-options>#popover-new-hours').empty();
						$('#popover-new-hours').append("Le "+moment(start).format('l')+" de "+moment(start).format('H:mm')+" à "+moment(end).format("H:mm"));
						$('#add-options>a').attr('href', 'resa_add.php');
						var pHeight = $('#add-options').height();
						$('#add-options').on('shown.bs.modal', function(e){
							$('#add-options').offset({top: position.top - pHeight*1.4, left: position.left - 50});
						});
						$('#add-options').popoverX('hide');
						$('#add-options').popoverX('toggle');
						sessionStorage.removeItem('end');
						sessionStorage.removeItem('start');
						sessionStorage.setItem('start', start);
						sessionStorage.setItem('end', end);
					}
				});

				$('[data-toggle="popover"]').popover();

				/*        $(window).resize(function(){
			docHeight = $(document).height();
			height = docHeight - xPos.top - 100;
			$('#calendar').fullCalendar('option', 'contentHeight', 650);
		});*/
				/*var filterOne = false, filterTwo = false, filterThree = false, filterCours = false, filterReservation = false;
				$("#filter-salle-1").change(function(){
					if(!filterOne){
						$("[salle='1']").hide();
						filterOne = true;
					} else {
						$("[salle='1']").show();
						filterOne = false;
						if(filterCours){
							$(":regex(id,^cours)").hide();
						}
					}
				});
				$("#filter-salle-2").change(function(){
					if(!filterTwo){
						$("[salle='2']").hide();
						filterTwo = true;
					} else {
						$("[salle='2']").show();
						filterTwo = false;
						if(filterCours){
							$(":regex(id,^cours)").hide();
						}
					}
				});
				$("#filter-salle-3").change(function(){
					if(!filterThree){
						$("[salle='3']").hide();
						filterThree = true;
					} else {
						$("[salle='3']").show();
						filterThree = false;
						if(filterCours){
							$(":regex(id,^cours)").hide();
						}
					}
				});
				$("#filter-cours").change(function(){
					if(!filterCours){
						$(":regex(id,^cours)").hide();
						if(filterOne){
							$("[salle='1']").hide();
						}
						if(filterTwo){
							$("[salle='2']").hide();
						}
						if(filterThree){
							$("[salle='3']").hide();
						}
						filterCours = true;
					} else {
						$("[id^='cours']").show();
						if(filterOne){
							$("[salle='1']").hide();
						}
						if(filterTwo){
							$("[salle='2']").hide();
						}
						if(filterThree){
							$("[salle='3']").hide();
						}
						filterCours = false;
					}
				});
				$("#filter-reservation").change(function(){
					if(!filterReservation){
						$(":regex(id,^reservation)").hide();
						if(filterOne){
							$("[salle='1']").hide();
						}
						if(filterTwo){
							$("[salle='2']").hide();
						}
						if(filterThree){
							$("[salle='3']").hide();
						}
						filterReservation = true;
					} else {
						$(":regex(id,^reservation)").show();
						if(filterOne){
							$("[salle='1']").hide();
						}
						if(filterTwo){
							$("[salle='2']").hide();
						}
						if(filterThree){
							$("[salle='3']").hide();
						}
						filterReservation = false;
					}
				});*/
			});
			/**$('#timepicker').timepicker({});
	$(document).ready(function(){
		$('#timepicker_locale_fin').timepicker({
			hourText: 'Heures',
			minuteText: 'Minutes',
			showPeriodLabels: 'false',
			timeSeparator: 'h',
			nowButtonText : 'Maintenant',
			showNowButton: 'true',
			closeButtonText: 'Fermer',
			showCloseButton: 'true',
			deselectButtonText: 'Déselectionner',
			showDeselectButton: 'true',
		});
	});**/
		</script>
		<script src="assets/js/check_calendar.js"></script>
	</body>
</html>
