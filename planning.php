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
               <h1 class="page-title"><span class="glyphicon glyphicon-time"></span> Planning des salles et locations</h1>
			  <div class="btn-toolbar">
                   <a href="cours_add.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Ajouter un cours</a>
                   <a href="resa_add.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-record"></span> Réserver une salle</a>
                   <a href="actions/salle_add.php" role="button" class="btn btn-primary disabled" data-title="Ajouter une salle" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter une salle</a>
                   <a href="actions/niveau_add.php" role="button" class="btn btn-primary disabled" data-title="Ajouter un niveau" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un niveau</a>
                   <a href="jours_chomes.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-leaf"></span> Jours Chômés...</a>
               </div> <!-- btn-toolbar -->
               <div id="display-planning" style="display:block;">
                    <div id="calendar" class="fc fc-ltr fc-unthemed"></div>
               </div> <!-- Display en Planning -->
           </div> <!-- col-sm-10 main -->
	    <div id="cours-options" class="popover popover-default">
			<div class="arrow"></div>
			<p style="font-weight:700;" id="popover-cours-title"></p>
			<p id="popover-cours-hours"></p>
			<a role="button" class="btn btn-default col-sm-12"><span class="glyphicon glyphicon-edit"></span> Détails >></a>
		</div>
        <div id="reservation-options" class="popover popover-default">
            <div class="arrow"></div>
            <p style="font-weight:700;" id="popover-reservation-title"></p>
            <p id="popover-reservation-hours"></p>
            <a class="btn btn-default col-sm-12"><span class="glyphicon glyphicon-edit"></span> Détails >></a>
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
        
        // Full calendar
        $('#calendar').fullCalendar({
            header:{
                left:'prev,next today',
                center:'title',
                right:'month, agendaWeek, agendaDay'
            },
            defaultView: 'agendaWeek',
            lang:'fr',
            editable: false,
            minTime: '9:00',
            allDaySlot: false,
            height: 'auto',
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
					switch(calEvent.prestation){
						case '6':
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
                    useOffsetForPos: true
                  };
                $('#'+calEvent.type+'-options').popoverX(options);
                $('#'+calEvent.type+'-options>p').empty();
                $('#popover-'+calEvent.type+'-title').append(calEvent.title);
                $('#popover-'+calEvent.type+'-hours').append("Le "+$.format.date(calEvent.start._i, "dd/MM/yyyy")+" de "+$.format.date(calEvent.start._i, "HH:mm")+" à "+$.format.date(calEvent.end._i, "HH:mm"));
                $('#'+calEvent.type+'-options>a').attr('href', calEvent.type+'_edit.php?id='+calEvent.id);
                $('#'+calEvent.type+'-options').popoverX('toggle');
			},
			dayClick: function(date, jsEvent, view){
				//console.log(date._d);
                //$(jsEvent.target).attr('id', 'click-id');
				//$('#add-options').popoverX('toggle');
/*				$(this).ekkoLightbox({
					remote: 'actions/cours_add.php',
					title: 'Ajouter un cours',
					onNavigate: false
				});*/
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