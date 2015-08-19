<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$date = new DateTime('now');
$time = $date->add(new DateInterval('P30D'))->format('Y-m-d');

$queryEcheances = $db->query("SELECT * FROM produits_echeances
										JOIN produits_adherents ON id_produit_adherent=produits_adherents.id_transaction
										JOIN produits ON id_produit=produits.produit_id
										JOIN users ON id_adherent=users.user_id
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
              <p id="current-time"></p>
               <h1 class="page-title"><span class="glyphicon glyphicon-repeat"></span> Echéances</h1>
				<div class="input-group input-group-lg search-form">
					<span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span></span>
					<input type="text" id="search" class="form-control" placeholder="Tapez pour rechercher...">
				</div>
               <div id="maturities-list">
               	<table class="table">
               		<thead>
               			<tr>
               				<th>Date <span class="glyphicon glyphicon-sort sort" data-sort="date"></span></th>
               				<th>Forfait associé <span class="glyphicon glyphicon-sort sort" data-sort="forfait-name"></span></th>
               				<th>Détenteur <span class="glyphicon glyphicon-sort sort" data-sort="user-name"></span></th>
               				<th>Montant <span class="glyphicon glyphicon-sort sort" data-sort="montant"></span></th>
               				<th>Statut Salsabor <span class="glyphicon glyphicon-sort sort" data-sort="status"></span></th>
               				<th>Statut Bancaire <span class="glyphicon glyphicon-sort sort" data-sort="bank"></span></th>
               			</tr>
               		</thead>
               		<tbody id="filter-enabled" class="list">
               			<?php while($echeances = $queryEcheances->fetch(PDO::FETCH_ASSOC)) {
               							switch($echeances["echeance_effectuee"]){
               								case 0:
               									$status = "En attente";
               									$statusClass = "info";
               									$statusChecked = "unchecked";
               									break;
               	
               								case 1:
               									$status = "Reçue";
               									$statusClass = "success";
               									$statusChecked = "checked";
               									break;
               	
               								case 2:
               									$status = "En retard";
               									$statusClass = "danger";
               									$statusChecked = "unchecked";
               									break;
               							}?>
               			<tr>
               							<td class="date"><?php echo date_create($echeances["date_echeance"])->format('d/m/Y');?></td>
               							<td class="forfait-name"><a href="forfait_adherent_details.php?id=<?php echo $echeances["id_transaction"];?>"><?php echo $echeances["produit_nom"];?></a></td>
               							<td class="user-name"><a href="eleve_details.php?id=<?php echo $echeances["user_id"];?>"><?php echo $echeances["user_prenom"]." ".$echeances["user_nom"]." (".$echeances["telephone"].")";?></a></td>
               							<td class="montant"><?php echo $echeances["montant"];?> €</td>
               							<td class="status">
             	             				<input type="checkbox" class="toggle-maturity" <?php echo $statusChecked;?> data-toggle="toggle" data-on="Reçue" data-off="<?php echo $status;?>" data-onstyle="success" data-offstyle="<?php echo $statusClass;?>">
               	             				<input type="hidden" name="echeance-id" value="<?php echo $echeances["id_echeance"];?>"></td>
               	             			<td class="bank">Encaissé <input name="statut_bank" id="statut-bank" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="<?php echo $echeances["statut_banque"];?>"></td>
               	              		</tr>
               			<?php } ?>
               		</tbody>
               	</table>
               </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script>
	   $(".toggle-maturity").change(function(){
		   var echeance_id = $(this).parents("td").children("input[name^='echeance']").val();
		   $.post("functions/validate_echeance.php", {echeance_id}).done(function(data){
			   showSuccessNotif(data);
		   })
	   })
	   
	   var options = {
		   valueNames: ['date', 'forfait-name', 'user-name', 'montant', 'status', 'bank']
	   };
	   var maturitiesList = new List('maturities-list', options);
	</script>
</body>
</html>