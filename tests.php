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
    $heure_debut = '19:00:00';
    $heure_fin = '21:30:00';
    $lieu = '1';
	$cumulatedPrice = 0;
    
    /** Conversion de la date **/
	$frequence_repetition = 7;
	$date_debut = '2015-06-08';
	$date_fin = '2015-06-30';
    $start = $date_debut." ".$heure_debut;
	$end = $date_debut." ".$heure_fin;
	(int)$nombre_repetitions = (strtotime($date_fin) - strtotime($date_debut))/(86400 * $frequence_repetition)+1;
$res = 0;
for($i = 1; $i < $nombre_repetitions; $i++){
	echo $start." - ".$end."<br>";
	$findResa = $db->prepare('SELECT COUNT(*) FROM cours WHERE cours_salle=? AND ((cours_start<=? AND cours_end>=?) OR (cours_start<=? AND cours_end>=?))');
$findResa->bindValue(1, $lieu);
	$findResa->bindValue(2, $start);
	$findResa->bindValue(3, $start);
	$findResa->bindValue(4, $end);
	$findResa->bindValue(5, $end);
	$findResa->execute();
	$res += $findResa->fetchColumn();
	$date_debut = strtotime($start.'+'.$frequence_repetition.'DAYS');
	$date_fin = strtotime($end.'+'.$frequence_repetition.'DAYS');
	$start = date("Y-m-d H:i:s", $date_debut);
	$end = date("Y-m-d H:i:s", $date_fin);
}
echo $res;
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