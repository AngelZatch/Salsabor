<?php
require_once "functions/db_connect.php";
/** Le fichier functions/cours.php contient toutes les fonctions relatives aux cours **/
require_once "functions/cours.php";

setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

/** Chaque trigger de tous les formulaires appelle une des fonctions dans functions/cours.php **/
// Ajout d'un cours
if(isset($_POST['addCours'])){
    addCours();
}
?>
<html>
<head>
    <title>Salsabor - Cours</title>
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
                   <a href="actions/resa_add.php" role="button" class="btn btn-primary disabled" data-title="Ajouter une réservation" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-record"></span> Réserver une salle</a>
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
               <input type="text" id="search" class="form-control" placeholder="Tapez n'importe quoi pour rechercher">
               <div class="table-responsive">
                   <table class="table table-striped table-hover" id="filter-enabled">
                       <thead>
                           <tr>
                               <th class="col-sm-2">Intitulé</th>
                               <th class="col-sm-2">Jour</th>
                               <th class="col-sm-2">Professeur</th>
                               <th class="col-sm-2">Niveau</th>
                               <th class="col-sm-2">Lieu</th>
                               <th class="col-sm-2">Actions</th>
                           </tr>
                       </thead>
                       <tbody>
                       <?php
                        $cours = $db->query('SELECT * FROM cours JOIN staff ON (prof_principal=staff.staff_id) JOIN niveau ON(cours_niveau=niveau.niveau_id) JOIN salle ON (cours_salle=salle.salle_id)');
                        while($row_cours = $cours->fetch(PDO::FETCH_ASSOC)){
                            echo "<tr>
                            <td class='col-sm-2'>".$row_cours['cours_intitule']."</td>
                            <td class='col-sm-2'>".date_create($row_cours['cours_start'])->format('d/m/Y')."<br>".(date_create($row_cours['cours_start'])->format('G:i'))." - ".(date_create($row_cours['cours_end'])->format('G:i'))."</td>
                            <td class='col-sm-2'>".$row_cours['prenom']." ".$row_cours['nom']."</td>
                            <td class='col-sm-2'><span class='label label-level-".$row_cours['niveau_id']."'>".$row_cours['niveau_name']."</span></td>
                            <td class='col-sm-2'>".$row_cours['salle_name']."</td>
                            <td class='col-sm-2'>
                            <form method='post'>
                            <div class='btn btn-group' role='group'>
                                <button type='submit' class='btn btn-default'><span class='glyphicon glyphicon-edit'></span></button>
                                <button type='submit' class='btn btn-default'><span class='glyphicon glyphicon-trash'></span></button>
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
                    <div id="calendar" class="fc fc-ltr fc-unthemed">
                        
                    </div>
               </div> <!-- Display en Planning -->
           </div> <!-- col-sm-10 main -->
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
                right:'agendaWeek, agendaDay'
            },
            defaultDate: 'today',
            defaultView: 'agendaWeek',
            lang:'fr',
            editable: true,
            hiddenDays: [0],
            timeZone: 'local',
            minTime: '13:00',
            allDaySlot: false,
            events:{
                url: 'functions/calendarfeed.php',
                type: 'POST',
                error: function(){
                    alert('Erreur pendant l\'obtention des évènements');
                }
            },
            backgroundColor: 'yellow',
            textColor:'black'
        });
        
        var $rows = $('#filter-enabled tr');
        $('#search').keyup(function(){
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
            $rows.show().filter(function(){
               var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
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
</body>
</html>