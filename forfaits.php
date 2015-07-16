<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryForfaits = $db->query("SELECT * FROM produits");
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forfaits | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-credit-card"></span> Forfaits</h1>
              <div class="btn-toolbar">
                   <a href="" role="button" class="btn btn-primary disabled"><span class="glyphicon glyphicon-plus"></span> Ajouter un forfait</a>
               </div> <!-- btn-toolbar -->
               <div class="table-responsive">
                   <table class="table table-striped table-hover">
                       <thead>
                           <tr>
                               <th>Produit</th>
                               <th>Volume de cours (heures)</th>
                               <th>Durée de validité (jours)</th>
                               <th>Tarif horaire</th>
                               <th>Tarif global</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php while($produits = $queryForfaits->fetch(PDO::FETCH_ASSOC)){ ?>
                           <tr>
                               <td><?php echo $produits["produit_nom"];?></td>
                               <td><?php echo $produits["volume_horaire"];?></td>
                               <td><?php echo $produits["validite_initiale"];?></td>
                               <td><?php echo $produits["tarif_horaire"];?> €</td>
                               <td><?php echo $produits["tarif_global"];?> €</td>
                           </tr>
                           <?php } ?>
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>