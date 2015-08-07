<?php
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
/** Le fichier functions/cours.php contient toutes les fonctions relatives aux cours **/
require_once "functions/cours.php";
require_once "functions/reservations.php";

setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

/** Chaque trigger de tous les formulaires appelle une des fonctions dans functions/cours.php **/
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
    <title>Planning | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
              <p id="current-time"></p>
              <h1 class="page-title"><span class="glyphicon glyphicon-time"></span> Planning des salles et locations</h1>
			  <div class="btn-toolbar">
                   <a href="cours_add.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Ajouter un cours</a>
                   <a href="resa_add.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-record"></span> Réserver une salle</a>
                   <a href="jours_chomes.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-leaf"></span> Jours Chômés...</a>
               </div> <!-- btn-toolbar -->
               <div id="display-planning" style="display:block;">
                    <div id="calendar" class="fc fc-ltr fc-unthemed"></div>
               </div> <!-- Display en Planning -->
           </div> <!-- col-sm-10 main -->
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
   <?php include "scripts.php";?>
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
            minTime: '9:00',
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
                //console.log(calEvent.type);
                //console.log(calEvent.priorite);
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
					switch(calEvent.prestation_id){
						case '6':
						case '7':
						case '8':
							element.css('background-color', '#2DF588');
							element.css('border-color', '#28DB7A');
							break;
							
						default:
							break;
					}
				}
			},
			eventClick: function(calEvent, element){
                var options = {     
                    target: '#'+$(this).attr('id'),
                    placement: 'top',
                    closeOtherPopovers: true,
                    useOffsetForpos: true
                  };
                var position = $(this).offset();
                var bWidth = $(this).width();
                $('#'+calEvent.type+'-options').popoverX(options);
                $('#'+calEvent.type+'-options>p').empty();
                $('#popover-'+calEvent.type+'-title').append(calEvent.title);
				$('#popover-'+calEvent.type+'-type').append(calEvent.prestation);
                $('#popover-'+calEvent.type+'-hours').append("Le "+$.format.date(calEvent.start._i, "dd/MM/yyyy")+" de "+$.format.date(calEvent.start._i, "HH:mm")+" à "+$.format.date(calEvent.end._i, "HH:mm"));
                $('#'+calEvent.type+'-options>a').attr('href', calEvent.type+'_edit.php?id='+calEvent.id);
                var pHeight = $('#'+calEvent.type+'-options').height();
                $('#'+calEvent.type+'-options').on('shown.bs.modal', function(e){
                    $('#'+calEvent.type+'-options').offset({top: position.top - pHeight*1.4, left: position.left - 50});
                });
                $('#'+calEvent.type+'-options').popoverX('toggle');
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
        var $rows = $('#filter-enabled tr');
        $('#search').keyup(function(){
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
            $rows.show().filter(function(){
               var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
        
        $('[data-toggle="popover"]').popover();
        
/*        $(window).resize(function(){
            docHeight = $(document).height();
            height = docHeight - xPos.top - 100;
            $('#calendar').fullCalendar('option', 'contentHeight', 650);
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