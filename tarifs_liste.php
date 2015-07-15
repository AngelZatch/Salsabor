<?php
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
include "functions/tarifs.php";

$id_prestations = array();
$id_horaires = array();

$arrayLieux = array(); 

$queryPrestations = $db->query('SELECT prestations_id, prestations_name FROM prestations WHERE est_resa=1');
$lieux = $db->query("SELECT * FROM salle")->fetchAll(PDO::FETCH_ASSOC);
foreach ($lieux as $row => $lieu){
	array_push($arrayLieux, $lieu["salle_id"]);
}
$periodes = $db->query("SELECT * FROM plages_reservations")->fetchAll(PDO::FETCH_ASSOC);
$queryTarifs = $db->prepare("SELECT prix_resa FROM tarifs_reservations WHERE type_prestation=? AND plage_resa=? AND lieu_resa=?");

if(isset($_POST['addTarifResa'])){
	addTarifResa();
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tarifs | Salsabor</title>
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
               </div> <!-- btn-toolbar -->
               <div class="panel-group" id="accordion">
               <?php while($prestations = $queryPrestations->fetch(PDO::FETCH_ASSOC)){?>
               	<div class="panel panel-default">
               		<div class="panel-heading" data-toggle="collapse" data-parent="#accordion" id="heading-<?php echo $prestations["prestations_id"];?>" href="#collapse-<?php echo $prestations["prestations_id"];?>"><?php echo $prestations["prestations_name"];?> <span class="glyphicon glyphicon-collapse-down"></span></div>
               		<div class="panel-collapse collapse in" id="collapse-<?php echo $prestations["prestations_id"];?>">
               			<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-hover">
									<thead>
										<tr>
											<th>Période</th>
											<?php foreach ($lieux as $row => $lieu){ ?>
											<th><?php echo $lieu["salle_name"];?></th>
											<?php }?>
										</tr>
									</thead>
									<tbody>
									<?php foreach($periodes as $row => $periode){ ?>
									<tr>
										<td>
											<?php echo $periode["plages_resa_id"];?>
										</td>
											<?php
												 $queryTarifs->bindParam(1, $prestations["prestations_id"]);
												 $queryTarifs->bindParam(2, $periode["plages_resa_id"]);
												 for($i = 0; $i < sizeof($arrayLieux); $i++){ 
													 $queryTarifs->bindParam(3, $arrayLieux[$i]);
													 $queryTarifs->execute();
													while($tarifs = $queryTarifs->fetch(PDO::FETCH_ASSOC)){ ?>
											 <td><span contenteditable="true">
												<?php echo $tarifs["prix_resa"]; ?>
												</span>
											</td>
										 <?php 
											 }
										 } ?>
									</tr>
									<?php } ?>
									</tbody>
								</table>
							</div>
               			</div>
               		</div>
               	</div>
			   <?php } ?>
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