<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d');
$queryEcheances = $db->query("SELECT * FROM produits_echeances
										JOIN produits_adherents ON id_produit_adherent=produits_adherents.id_transaction
										JOIN produits ON id_produit=produits.produit_id
										JOIN adherents ON id_adherent=adherents.eleve_id
										WHERE echeance_effectuee=2");
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Echeances | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-repeat"></span> Echéances</h1>
               <table class="table">
               	<thead>
               		<tr>
               			<th>Date</th>
               			<th>Forfait associé</th>
               			<th>Détenteur</th>
               			<th>Montant</th>
               			<th>Statut</th>
               		</tr>
               	</thead>
               	<tbody>
               		<?php while($echeances = $queryEcheances->fetch(PDO::FETCH_ASSOC)) {?>
               		<tr>
						<td><?php echo date_create($echeances["date_echeance"])->format('d/m/Y');?></td>
						<td><?php echo $echeances["produit_nom"];?></td>
						<td><?php echo $echeances["eleve_prenom"]." ".$echeances["eleve_nom"];?></td>
						<td><?php echo $echeances["montant"];?> €</td>
						<td><?php echo $echeances["echeance_effectuee"];?></td>
              		</tr>
               		<?php } ?>
               	</tbody>
               </table>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>