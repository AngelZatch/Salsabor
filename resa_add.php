<?php
require_once 'functions/db_connect.php';
include 'librairies/fpdf.php';
$db = PDOFactory::getConnection();
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
              <br>
               <div class="col-sm-9">
               	<form action="planning.php" method="post" class="form-horizontal" role="form" id="add_resa">
					 <div class="btn-toolbar">
					   <a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
					   <input type="submit" name="addResa" role="button" class="btn btn-primary" value="ENREGISTRER">
					   <a href="planning.php" role="button" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Supprimer</a>
              	    </div> <!-- btn-toolbar -->   
              	    <br>
               	    <div class="form-group">
               	        <label for="identite" class="col-sm-3 control-label">Demandeur <span class="mandatory">*</span></label>
               	        <div class="col-sm-9">
               	            <input type="text" name="identite" id="resa_add_identite" class="form-control" placeholder="Entrez un nom">
               	        </div>
               	    </div>
               	    <div class="form-group">
               	        <label for="prestation" class="col-sm-3 control-label">Activité <span class="mandatory">*</span></label>
               	        <div class="col-sm-9">
               	           <select name="prestation" id="prestation" class="form-control" onChange="checkCalendar(true, false)">
               	           <?php
               	            $prestations = $db->query('SELECT * FROM prestations WHERE est_resa=1');
               	            while($row_prestations = $prestations->fetch(PDO::FETCH_ASSOC)){
               	                echo "<option value=".$row_prestations['prestations_id'].">".$row_prestations['prestations_name']."</option>";
               	            }
               	            ?>
               	            </select>
               	        </div>
               	    </div>
               	    <div class="form-group">
               	        <label for="date_debut" class="col-sm-3 control-label">Date <span class="mandatory">*</span></label>
               	        <div class="col-sm-9"><input type="date" class="form-control" name="date_debut" id="date_debut" onChange="checkCalendar(true, false)"></div>
               	    </div>
               	    <div class="form-group">
               	        <fieldset>
               	            <label for="heure_debut" class="col-sm-3 control-label">Début à <span class="mandatory">*</span></label>
               	            <div class="col-sm-9"><input type="time" class="form-control" id="heure_debut" name="heure_debut" onChange="checkCalendar(true, false)"></div>
               	            <label for="heure_fin" class="col-sm-3 control-label">Fin à <span class="mandatory">*</span></label>
               	            <div class="col-sm-9"><input type="time" class="form-control" id="heure_fin" name="heure_fin" onChange="checkCalendar(true, false)"></div>
               	        </fieldset>
               	    </div>
               	    <div class="form-group">
               	        <label for="lieu" class="col-sm-3 control-label">Salle <span class="mandatory">*</span></label>
               	        <div class="col-sm-9">
               	           <select name="lieu" class="form-control" id="lieu" onChange="checkCalendar(true, false)">
               	           <?php
               	            $lieux = $db->query('SELECT * FROM salle');
               	            while($row_lieux = $lieux->fetch(PDO::FETCH_ASSOC)){
               	                echo "<option value=".$row_lieux['salle_id'].">".$row_lieux['salle_name']."</option>";
               	            }
               	            $lieux->closeCursor();
               	            ?>
               	            </select>          
               	        </div>
               	    </div>
                    <div class="form-group">
						<label for="priorite" class="col-sm-3 control-label">Réservation payée</label>
						<div class="col-sm-9">
						    <input name="priorite" id="priorite" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
						    <label for="priorite">Une réservation payée ne peut plus être supprimée au profit d'un cours.</label>
						</div>
					</div>
                    <div class="form-group" id="prix_reservation">
                        <label for="prix_resa" class="col-sm-3 control-label">Prix de la réservation : </label>
                        <div class="col-sm-9">
                            <input type="text" name="prix_resa" id="prix_calcul" class="form-control">
                            <input type="checkbox" unchecked data-toggle="toggle" data-on="Payée" data-off="Due" data-onstyle="success" data-offstyle="danger" style="float:left;" id="paiement">
                            <input type="hidden" name="paiement" id="paiement-sub" value="0">
                        </div>
                    </div>
               	    <div class="align-right">
               	    	<p class="" id="error_message"></p>
               	    </div>
               	</form>
               </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script src="assets/js/check_calendar.js"></script>
      <script>
   if($('#priorite').attr('value') == 0){
	   $('#prix_reservation').hide();
   }
	   $('#priorite').change(function(){
		   $('#prix_reservation').toggle('600');
	   })
	$('#paiement').change(function(){
		var state = $('#paiement').prop('checked');
		if(state){
			$('#paiement-sub').val(1);
		} else {
			$('#paiement-sub').val(0);
		}
	});
	</script> 
</body>
</html>