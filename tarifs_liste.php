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
                /** On obtient le nombre maximum de prestations pour pouvoir afficher plus tard toutes les prestations qui nous correspondent **/
                $count = $db->query('SELECT COUNT(*) FROM prestations');
                $maxPrestations = $count->fetchColumn();
                /** On obtient le nombre d'horaires différents pour tout **/
                $liste_tarifs = $db->prepare('SELECT DISTINCT heure_debut_resa FROM tarifs_reservations');
                $liste_tarifs->execute();
                $array_heures = $liste_tarifs->fetchAll(PDO::FETCH_COLUMN);
                /** On obtient la liste des jours **/
                $liste_tarifs = $db->prepare('SELECT DISTINCT jour_resa FROM tarifs_reservations');
                $liste_tarifs->execute();
                $array_jours = $liste_tarifs->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_COLUMN);
                for($k = 0; $k < sizeof($array_jours); $k++){
                    echo "<div class='table-responsive'>
                            <table class='table table-striped table-hover'>
                                <thead>
                                    <tr>
                                        <th class='col-sm-3'></th>";
                    $liste_types = $db->query('SELECT prestations_id, prestations_name FROM prestations WHERE est_resa=1');
                    while($row_liste_types = $liste_types->fetch(PDO::FETCH_ASSOC)){
                        echo "<th class='col-sm-2'>".$row_liste_types['prestations_name']."</th>";
                    }
                   echo "</tr>
                        </thead>";
                    /** Affichage des jours **/
                    echo "<tbody>";
                    for($j = 0; $j < sizeof($array_heures); $j++){
                        /** Pour chaque heure, on obtient tous les tarifs pour toutes les activités **/
                        $liste_tarifs = $db->prepare('SELECT * FROM tarifs_reservations WHERE heure_debut_resa=? AND jour_resa=?');
                        $liste_tarifs->bindValue(1, $array_heures[$j]);
                        $liste_tarifs->bindValue(2, $array_jours[$k]);
                        $liste_tarifs->execute();
                        echo "<tr>";
                        while($row_liste_tarifs = $liste_tarifs->fetch(PDO::FETCH_ASSOC)){
                            /** Affichage des horaires par jour**/
                            echo "<td class='col-sm-4'>".$row_liste_tarifs['heure_debut_resa']." - ".$row_liste_tarifs['heure_fin_resa']."</td>";
                            /** Pour chaque heure et chaque jour, on obtient le tarif et le type d'activité **/
                            $liste_tarifs = $db->prepare('SELECT prix_resa, type_prestation FROM tarifs_reservations WHERE type_prestation=? AND heure_debut_resa=? AND jour_resa=?');
                            for($i = 1; $i <= $maxPrestations; $i++){
                                $liste_tarifs->bindValue(1, $i, PDO::PARAM_INT);
                                $liste_tarifs->bindValue(2, $array_heures[$j]);
                                $liste_tarifs->bindValue(3, $array_jours[$k]);
                                $liste_tarifs->execute();
                                while($row_definitive = $liste_tarifs->fetch(PDO::FETCH_ASSOC)){
                                    /** Affichage des prix par horaires, par jour et par activité **/
                                        echo "<td class='col-sm-2'>".$row_definitive['prix_resa']." € TTC</td>";
                                }
                            }
                            echo "</tr>";
                        }
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