<?php
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
/** Le fichier functions/cours.php contient toutes les fonctions relatives aux cours **/
require_once "functions/cours.php";
require_once "functions/reservations.php";

setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

/** Chaque trigger de tous les formulaires appelle une des fonctions dans functions/cours.php **/
// Ajout d'un cours
if(isset($_POST['addCours'])){
    addCours();
}

// Ajout d'une réservation
if(isset($_POST['addResa'])){
	addResa();
}

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
    <title>Planning</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-time"></span> Planning des salles et Réservations</h1>
			  <div class="btn-toolbar">
                   <a href="actions/cours_add.php" role="button" class="btn btn-primary" data-title="Ajouter un cours" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un cours</a>
                   <a href="resa_add.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-record"></span> Réserver une salle</a>
                   <a href="actions/salle_add.php" role="button" class="btn btn-primary disabled" data-title="Ajouter une salle" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter une salle</a>
                   <a href="actions/niveau_add.php" role="button" class="btn btn-primary disabled" data-title="Ajouter un niveau" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un niveau</a>
               </div> <!-- btn-toolbar -->
               <div id="display-liste" style="display:none;">
               <div class="menu-bar">
                   <ul class="nav nav-pills" id="tri-cours">
                      <li role="presentation"><a onClick="toggleListePlanning()"><span class="glyphicon glyphicon-calendar"></span> Planning</a></li>
                      <li role="presentation" class="active"><a onClick="toggleListePlanning()"><span class="glyphicon glyphicon-list"></span> Liste</a></li>
                   </ul>
               </div> <!-- menu-bar -->
               <br><br>
               <div class="input-group input-group-lg">
               <span class="glyphicon glyphicon-filter input-group-addon" id="basic-addon1"></span>
               <input type="text" id="search" class="form-control" placeholder="Tapez n'importe quoi pour rechercher" aria-describedby="basic-addon1">
               </div>
               <br>
               <div class="table-responsive">
                   <table class="table table-striped table-hover">
                       <thead>
                           <tr>
                               <th class="col-sm-2">Intitulé</th>
                               <th class="col-sm-1">Professeur</th>
                               <th class="col-sm-1">Jour</th>
                               <th class="col-sm-2">Type de cours</th>
                               <th class="col-sm-2">Niveau</th>
                               <th class="col-sm-2">Lieu</th>
                               <th class="col-sm-2">Actions</th>
                           </tr>
                       </thead>
                       <tbody id="filter-enabled">
                       <?php
                        $cours = $db->query('SELECT * FROM cours JOIN professeurs ON (prof_principal=professeurs.prof_id) JOIN niveau ON(cours_niveau=niveau.niveau_id) JOIN salle ON (cours_salle=salle.salle_id) JOIN prestations ON(cours_type=prestations.prestations_id)');
                        while($row_cours = $cours->fetch(PDO::FETCH_ASSOC)){
                            echo "<tr>
                            <td class='col-sm-2'>".$row_cours['cours_intitule']." (".$row_cours['cours_suffixe'].")</td>
                            <td class='col-sm-1'>".$row_cours['prenom']." ".$row_cours['nom']."</td>
                            <td class='col-sm-1'>".date_create($row_cours['cours_start'])->format('d/m/Y')."<br>".(date_create($row_cours['cours_start'])->format('G:i'))." - ".(date_create($row_cours['cours_end'])->format('G:i'))."</td>
                            <td class='col-sm-2'>".$row_cours['prestations_name']."</td>
                            <td class='col-sm-2 level-display'><span class='label label-level-".$row_cours['niveau_id']."'>".$row_cours['niveau_name']."</span></td>
                            <td class='col-sm-2'>".$row_cours['salle_name']."</td>
                            <td class='col-sm-2'>
                            <form method='post'>
                            <div class='btn btn-group' role='group'>
                                <button type='submit' class='btn btn-default'><span class='glyphicon glyphicon-edit'></span></button>
                                <button type='button' class='btn btn-default' data-toggle='popover-x' data-placement='bottom' data-target='#delete-options-".$row_cours['cours_id']."'><span class='glyphicon glyphicon-trash'></span></button>
                                <div id='delete-options-".$row_cours['cours_id']."' class='popover popover-default'>
                                    <div class='arrow'></div>
                                        <p style='font-weight:700;'>Supprimer...</p>
                                        <button type='submit' name='deleteCoursOne' class='btn btn-default' style='width:11em;'>Cet évènement</button>
                                        <button type='submit' name='deleteCoursNext' class='btn btn-default' style='width:11em;'>Tous les suivants</button>
                                        <button type='submit' name='deleteCoursAll' class='btn btn-default' style='width:11em;'>Toute la série</button>
                                </div>
                            </div>
                            <input type='hidden' name='id' value=".$row_cours['cours_id'].">
                            </form>
                            </td>
                            </tr>";
                        };
                        ?>
                       </tbody>
                   </table>
               </div> <!-- table-responsive -->
               </div> <!-- Display en Liste -->
               <div id="display-planning" style="display:block;">
                    <div class="menu-bar">
                        <ul class="nav nav-pills" id="tri-cours">
                           <li role="presentation" class="active"><a onClick="toggleListePlanning()"><span class="glyphicon glyphicon-calendar"></span> Planning</a></li>
                            <li role="presentation"><a onClick="toggleListePlanning()"><span class="glyphicon glyphicon-list"></span> Liste</a></li>
                        </ul>
                    </div> <!-- menu-bar -->
                    <br><br>
                    <div id="calendar" class="fc fc-ltr fc-unthemed"></div>
               </div> <!-- Display en Planning -->
           </div> <!-- col-sm-10 main -->
	   <div id="cours-options" class="popover popover-default">
			<div class="arrow"></div>
			<p style="font-weight:700;" id="popover-cours-title"></p>
			<p id="popover-cours-hours"></p>
			<a role="button" class="btn btn-default col-sm-12"><span class="glyphicon glyphicon-edit"></span> Détails >></a>
			<!--<form method="post">
				<button class="btn btn-default">Supprimer ce cours</button>
				<button class="btn btn-default">Supprimer tous les suivants</button>
				<button class="btn btn-default">Supprimer toute la série</button>
			</form>-->
		</div>
		<div id="reservation-options" class="popover popover-default">
			<div class="arrow"></div>
			<p style="font-weight:700;" id="popover-reservation-title"></p>
			<p id="popover-reservation-hours"></p>
				<a class="btn btn-default col-sm-12"><span class="glyphicon glyphicon-edit"></span> Détails >></a>
			<!--<form method="post">
				<button class="btn btn-default">Supprimer</button>
			</form>-->
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