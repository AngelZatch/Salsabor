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
    $date = '2015-06-29 '.$heure_debut;
    if($date <= 5){
        $plage_resa = 1;
    }
    else if($date == 6){
        $plage_resa = 2;
    }
    else $plage_resa = 3;
    
$findResa = $db->prepare('SELECT COUNT(*) FROM cours WHERE cours_salle=? AND cours_start=?');
$findResa->bindValue(1, $lieu);
$findResa->bindValue(2, $date);
$findResa->execute();
$res = $findResa->fetchColumn();
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