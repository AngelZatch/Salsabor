<?php
require_once "functions/db_connect.php";
require_once "functions/tarifs.php";

if(isset($_POST['addTarifResa'])){
    addTarifResa();
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Template - Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-scale"></span> Tarifs</h1>
               <div class="btn-toolbar">
                   <a href="actions/tarifs_resa_add.php" role="button" class="btn btn-primary" data-title="Ajouter un tarif Réservation" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un tarif Réservation</a>
                   <a href="actions/tarifs_profs_add.php" role="button" class="btn btn-primary disabled" data-title="Ajouter un tarif Professeur" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un tarif Professeur</a>
               </div> <!-- btn-toolbar -->
              <div class="menu-bar">
                   <ul class="nav nav-pills" id="tri-cours">
                      <li role="presentation" class="active"><a onClick="toggleListePlanning()"><span class="glyphicon glyphicon-time"></span> Réservations</a></li>
                      <li role="presentation"><a onClick="toggleListePlanning()"><span class="glyphicon glyphicon-user"></span> Professeurs</a></li>
                   </ul>
               </div> <!-- menu-bar -->
                <?php
                $jours = array('Semaine', 'Samedi', 'Dimanche');
                for($i = 1; $i <= 3; $i++){
                    echo "<div class='table-responsive'>
                            <h2>".$jours[$i-1]."</h2>
                                <table class='table table-striped table-hover'>
                                    <thead>
                                        <tr>
                                            <th class='col-sm-3'></th>";
                    $liste_types = $db->query('SELECT prestations_id, prestations_name FROM prestations WHERE est_resa=1');
                    $id_prestations = array();
                    while($row_liste_types = $liste_types->fetch(PDO::FETCH_ASSOC)){
                        echo "<th class='col-sm-2'>".$row_liste_types['prestations_name']."</th>";
                        array_push($id_prestations, $row_liste_types['prestations_id']);
                    }
                    echo "</tr></thead><tbody>";

                    /** Get les horaires et les id associés **/
                    $liste_horaires = $db->prepare('SELECT * FROM plages_reservations WHERE plages_resa_jour=?');
                    $liste_horaires->bindValue(1, $i);
                    $liste_horaires->execute();
                    while($row_liste_horaires = $liste_horaires->fetch(PDO::FETCH_ASSOC)){
                        echo "<tr><td class='col-sm-2'>".$row_liste_horaires['plages_resa_debut']." - ".$row_liste_horaires['plages_resa_fin']."</td>";
                        /** Get les tarifs associés à l'id qu'on a **/
                        $tarifs_valeur = $db->prepare('SELECT * FROM tarifs_reservations WHERE plage_resa=? ORDER BY type_prestation ASC');
                        $tarifs_valeur->bindValue(1, $row_liste_horaires['plages_resa_id']);
                        $tarifs_valeur->execute();
                        while($row_tarifs_valeur = $tarifs_valeur->fetch(PDO::FETCH_ASSOC)){
                            echo "<td class='col-sm-2'>".$row_tarifs_valeur['prix_resa']."€ TTC</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                }
               ?>
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
        });
    </script>
</body>
</html>