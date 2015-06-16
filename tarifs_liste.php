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
                $id_prestations = array();
                $id_horaires = array();
                echo "<div class='table-responsive'>
                        <table class='table table-striped table-hover'>
                            <thead>
                                <tr>
                                    <th class='col-sm-3'></th>";
                    $liste_types = $db->query('SELECT prestations_id, prestations_name FROM prestations WHERE est_resa=1');
                    while($row_liste_types = $liste_types->fetch(PDO::FETCH_ASSOC)){
                        echo "<th class='col-sm-2'>".$row_liste_types['prestations_name']."</th>";
                        array_push($id_prestations, $row_liste_types['prestations_id']);
                    }
                    echo "</tr></thead><tbody>";
                    for($i = 1; $i <= 3; $i++){
                        /** Get les horaires et les id associés **/
                        $liste_horaires = $db->prepare('SELECT * FROM plages_reservations WHERE plages_resa_jour=?');
                        $liste_horaires->bindValue(1, $i);
                        $liste_horaires->execute();
                        while($row_liste_horaires = $liste_horaires->fetch(PDO::FETCH_ASSOC)){
                            $id_horaires[] = array($row_liste_horaires['plages_resa_id'],
                                                   $row_liste_horaires['plage_resa_nom'],
                                                   date_create($row_liste_horaires['plages_resa_debut'])->format('H:i'),
                                                   date_create($row_liste_horaires['plages_resa_fin'])->format('H:i'));
                        }
                    }
                    for($j = 0; $j < sizeof($id_horaires); $j++){
                        echo "<tr><td class='col-sm-2'>".$id_horaires[$j][1]." (".$id_horaires[$j][2]." - ".$id_horaires[$j][3].")</td>";
                        for($k = 0; $k < sizeof($id_prestations); $k++){
                            /** Get les tarifs associés à l'id qu'on a **/
                            $tarif = $db->prepare('SELECT prix_resa FROM tarifs_reservations WHERE type_prestation=? AND plage_resa=? AND lieu_resa=1');
                            $tarif->bindValue(1, $id_prestations[$k]);
                            $tarif->bindValue(2, $id_horaires[$j][0]);
                            $tarif->execute();
                            $row_tarifs = $tarif->fetch(PDO::FETCH_ASSOC);
                            if(isset($row_tarifs['prix_resa'])){
                                echo "<td class='col-sm-2'>".$row_tarifs['prix_resa']."€ TTC</td>";
                            } else {
                                echo "<td class='col-sm-2'> -- € TTC </td>";
                            }
                        }
                        echo "</tr>";
                    }
                echo "</tbody></table></div>";
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