<?php
require_once 'functions/db_connect.php';
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
               <h1 class="page-title"><span class="glyphicon glyphicon-pencil"></span> Page Test !</h1>
               <?php
    $prestation = '1';
    $heure_debut = '17:00:00';
    $heure_fin = '18:30:00';
    $lieu = '1';
	$cumulatedPrice = 0;
    
    
    /** Conversion de la date **/
    $date = date_create('2015-06-19')->format('N');
    if($date <= 5){
        $plage_resa = 1;
    }
    else if($date == 6){
        $plage_resa = 2;
    }
    else $plage_resa = 3;
    
	$duration = (strtotime($heure_fin) - strtotime($heure_debut))/1800;
for($i = 0; $i < $duration; $i++){
	$heure_debut_decalee = date("H:i:s", strtotime($heure_debut) + ($i * 30 * 60));
	$findHours = $db->prepare('SELECT * FROM tarifs_reservations JOIN plages_reservations ON (plage_resa=plages_reservations.plages_resa_id) WHERE type_prestation=? AND plages_resa_jour=? AND plages_resa_debut<=? AND plages_resa_fin>? AND lieu_resa=?');
	$findHours->bindValue(1, $prestation);
	$findHours->bindValue(2, $plage_resa);
	$findHours->bindValue(3, $heure_debut_decalee);
	$findHours->bindValue(4, $heure_debut_decalee);
	$findHours->bindValue(5, $lieu);
	$findHours->execute();
	$res = $findHours->fetch(PDO::FETCH_ASSOC);
	echo "Pour l'heure ".$heure_debut_decalee." le prix de la réservation est de : ".$res['prix_resa']."<br>";
	$cumulatedPrice += 0.5 * $res['prix_resa'];
}
   echo $cumulatedPrice." €";
                    ?>
            </form>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>
<script>
</script>