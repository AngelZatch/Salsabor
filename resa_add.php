<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
require_once 'functions/reservations.php';

$queryPrestations = $db->query('SELECT * FROM prestations WHERE est_resa=1');
$queryLieux = $db->query('SELECT * FROM salle WHERE est_salle_cours=1');

// Ajout d'une réservation
if(isset($_POST['addResa'])){
	addResa();
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réserver une salle | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
              <h1 class="page-title"><span class="glyphicon glyphicon-record"></span> Effectuer une réservation</h1>
               <div class="col-sm-9" id="solo-form">
               	<form action="resa_add.php" method="post" target="_blank" class="form-horizontal" role="form" id="add_resa">
					 <div class="btn-toolbar">
					   <a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour au planning</a>
					   <input type="submit" name="addResa" role="button" class="btn btn-primary confirm-add" value="ENREGISTRER" id="submit-button" disabled>
              	    </div> <!-- btn-toolbar -->   
              	    <div class="alert alert-success" id="user-added" style="display:none;">Adhérent ajouté avec succès</div>
              	    <div class="class alert alert-danger" id="user-error" style="display:none;">Erreur. Certains champs sont vides</div>
               	    <div class="form-group">
               	        <label for="identite" class="col-sm-3 control-label">Demandeur <span class="span-mandatory">*</span></label>
               	        <div class="col-sm-9">
               	            <input type="text" name="identite_prenom" id="identite_prenom" class="form-control mandatory" placeholder="Prénom" onChange="ifAdherentExists()">
               	            <input type="text" name="identite_nom" id="identite_nom" class="form-control mandatory" placeholder="Nom" onChange="ifAdherentExists()">
               	        </div>
               	        <div class="align-right">
							<p class="error-alert" id="err_adherent"></p>
							<a href="#user-details" role="button" class="btn btn-primary" value="create-user" id="create-user" style="display:none;" data-toggle="collapse" aria-expanded="false" aria-controls="userDetails">Créer</a>
               	        </div>
               	        <div id="user-details" class="collapse">
               	        	<div class="well">
               	        		<div class="form-group">
               	        			<input type="text" name="rue" id="rue" placeholder="Adresse" class="form-control">
								</div>
								<div class="form-group">
									<input type="text" name="code_postal" id="code_postal" placeholder="Code Postal" class="form-control">
								</div>
								<div class="form-group">
									<input type="text" name="ville" id="ville" placeholder="Ville" class="form-control">
								</div>
								<div class="form-group">
									<input type="text" name="mail" id="mail" placeholder="Adresse mail" class="form-control">
								</div>
								<div class="form-group">
									<input type="text" name="telephone" id="telephone" placeholder="Numéro de téléphone" class="form-control">
								</div>
								<div class="form-group">
									<input type="date" name="date_naissance" id="date_naissance" class="form-control">
								</div>
               	        		<a class="btn btn-primary" onClick="addAdherent()">AJOUTER</a>
               	        	</div>
               	        </div>
               	    </div>
               	    <div class="form-group">
               	        <label for="prestation" class="col-sm-3 control-label">Activité <span class="span-mandatory">*</span></label>
               	        <div class="col-sm-9">
               	           <select name="prestation" id="prestation" class="form-control" onChange="checkCalendar(true, false)">
               	           <?php while($prestations = $queryPrestations->fetch(PDO::FETCH_ASSOC)){?>
               	                <option value="<?php echo $prestations['prestations_id'];?>"><?php echo $prestations['prestations_name'];?></option>";
               	            <?php } ?>
               	            </select>
               	        </div>
               	    </div>
               	    <div class="form-group">
               	        <label for="date_debut" class="col-sm-3 control-label">Date <span class="span-mandatory">*</span></label>
               	        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="date" class="form-control" name="date_debut" id="date_debut" onChange="checkHoliday()">
                                <span role="buttton" class="input-group-btn"><a class="btn btn-default" role="button" date-today="true">Insérer aujourd'hui</a></span>
                            </div>
                            <p class="error-alert" id="holiday-alert"></p>
               	        </div>
               	    </div>
               	    <div class="form-group">
               	        <fieldset>
               	            <label for="heure_debut" class="col-sm-3 control-label">Début à <span class="span-mandatory">*</span></label>
               	            <div class="col-sm-9"><input type="time" class="form-control" id="heure_debut" name="heure_debut" onChange="checkCalendar(true, false)"></div>
               	            <label for="heure_fin" class="col-sm-3 control-label">Fin à <span class="span-mandatory">*</span></label>
               	            <div class="col-sm-9"><input type="time" class="form-control" id="heure_fin" name="heure_fin" onChange="checkCalendar(true, false)"></div>
               	        </fieldset>
               	    </div>
               	    <div class="form-group">
               	        <label for="lieu" class="col-sm-3 control-label">Salle <span class="span-mandatory">*</span></label>
               	        <div class="col-sm-9">
               	           <select name="lieu" class="form-control" id="lieu" onChange="checkCalendar(true, false)">
               	           <?php while($lieux = $queryLieux->fetch(PDO::FETCH_ASSOC)){?>
               	                <option value="<?php echo $lieux['salle_id'];?>"><?php echo $lieux['salle_name'];?></option>;
               	            <?php } ?>
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
                               <div class="input-group">
                                    <span class="input-group-addon" id="currency-addon">€</span>
                                    <input type="text" name="prix_resa" id="prix_calcul" class="form-control" aria-describedby="currency-addon">
                                </div>
                            <input type="checkbox" unchecked data-toggle="toggle" data-on="Payée" data-off="Due" data-onstyle="success" data-offstyle="danger" style="float:left;" id="paiement">
                            <input type="hidden" name="paiement" id="paiement-sub" value="0">
                        </div>
                    </div>
               	    <div class="align-right">
               	    	<p class="error-alert" id="error_message"></p>
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
        });
        $('#paiement').change(function(){
            var state = $('#paiement').prop('checked');
            if(state){
                $('#paiement-sub').val(1);
            } else {
                $('#paiement-sub').val(0);
            }
        });
       
       $(".mandatory").blur(checkMandatory);
       
       $(document).ready(function(){
       	var start = sessionStorage.getItem('start');
		if(start != null){
			var format_start = new Date(start).toISOString();
			var end = sessionStorage.getItem('end');
			var format_end = new Date(end).toISOString();
		} else {
			var format_start = new Date().toISOString();
			var format_end = new Date().toISOString();
		}
		var start_day = moment(format_start).format('YYYY-MM-DD');
		var start_hour = moment(format_start).startOf('hour').add(1, 'h').format('HH:mm');
		var end_hour = moment(format_end).startOf('hour').add(2, 'h').format('HH:mm');
           
           $("#date_debut").val(start_day);
           $("#heure_debut").val(start_hour);
           $("#heure_fin").val(end_hour);
           
           sessionStorage.removeItem('end');
           sessionStorage.removeItem('start');
       });
	</script> 
</body>
</html>