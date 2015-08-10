<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$date = new DateTime('now');
$time = $date->add(new DateInterval('P30D'))->format('Y-m-d');

$queryEcheances = $db->query("SELECT * FROM produits_echeances
										JOIN produits_adherents ON id_produit_adherent=produits_adherents.id_transaction
										JOIN produits ON id_produit=produits.produit_id
										JOIN adherents ON id_adherent=adherents.eleve_id
										WHERE date_echeance<='$time' ORDER BY date_echeance ASC");
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
				<div class="input-group input-group-lg search-form">
					<span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span></span>
					<input type="text" id="search" class="form-control" placeholder="Tapez pour rechercher...">
				</div>
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
               	<tbody id="filter-enabled">
               		<?php while($echeances = $queryEcheances->fetch(PDO::FETCH_ASSOC)) {
						switch($echeances["echeance_effectuee"]){
							case 0:
								$status = "En attente";
								$statusClass = "info";
								break;

							case 1:
								$status = "Payée";
								$statusClass = "success";
								break;

							case 2:
								$status = "En retard";
								$statusClass = "danger";
								break;
						}?>
               		<tr>
						<td><?php echo date_create($echeances["date_echeance"])->format('d/m/Y');?></td>
						<td><?php echo $echeances["produit_nom"];?></td>
						<td><?php echo $echeances["eleve_prenom"]." ".$echeances["eleve_nom"];?></td>
						<td><?php echo $echeances["montant"];?> €</td>
						<td><input type="checkbox" unchecked data-toggle="toggle" data-on="Payée" data-off="<?php echo $status;?>" data-onstyle="success" data-offstyle="<?php echo $statusClass;?>" id="echeance"></td>
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