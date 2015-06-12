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
               <div class="table-responsive">
                   <table class="table table-striped table-hover">
                       <thead>
                           <tr>
                            <th class="col-sm-3"></th>
                             <?php
                            $liste_types = $db->query('SELECT prestations_id, prestations_name FROM prestations WHERE est_resa=1');
                            while($row_liste_types = $liste_types->fetch(PDO::FETCH_ASSOC)){
                                echo "<th class='col-sm-2'>".$row_liste_types['prestations_name']."</th>";
                            }
                            ?>
                           </tr>
                       </thead>
                            <?php
                            $count = $db->query('SELECT COUNT(*) FROM prestations');
                            $maxPrestations = $count->fetchColumn();

                            $liste_tarifs = $db->prepare('SELECT DISTINCT heure_debut_resa FROM tarifs_reservations');
                            $liste_tarifs->execute();
                            $array_heures = $liste_tarifs->fetchAll(PDO::FETCH_COLUMN);
                            echo "<tbody>";
                            for($j = 0; $j < sizeof($array_heures); $j++){
                                $liste_tarifs = $db->prepare('SELECT * FROM tarifs_reservations WHERE heure_debut_resa=?');
                                $liste_tarifs->bindValue(1, $array_heures[$j]);
                                $liste_tarifs->execute();
                                echo "<tr>";
                                while($row_liste_tarifs = $liste_tarifs->fetch(PDO::FETCH_ASSOC)){
                                    echo "<td class='col-sm-4'>".$row_liste_tarifs['heure_debut_resa']." - ".$row_liste_tarifs['heure_fin_resa']."</td>";
                                    $liste_tarifs = $db->prepare('SELECT prix_resa, type_prestation FROM tarifs_reservations WHERE type_prestation=? AND heure_debut_resa=?');
                                    for($i = 1; $i <= $maxPrestations; $i++){
                                        $liste_tarifs->bindValue(1, $i, PDO::PARAM_INT);
                                        $liste_tarifs->bindValue(2, $array_heures[$j]);
                                        $liste_tarifs->execute();
                                        while($row_definitive = $liste_tarifs->fetch(PDO::FETCH_ASSOC)){
                                                echo "<td class='col-sm-2'>".$row_definitive['prix_resa']."€</td>";
                                        }
                                    }
                                    echo "</tr>";
                                }
                            }
                            echo "</tbody>";
                           ?>
                   </table>
               </div>
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