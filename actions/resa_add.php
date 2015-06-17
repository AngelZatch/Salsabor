<?php
require_once "../functions/db_connect.php";
?>
<div class="row row-custom">
<form action="cours_liste.php" method="post" class="form-horizontal" role="form" id="add_resa">
    <div class="form-group">
        <label for="identite" class="col-sm-3 control-label">Demandeur <span class="mandatory">*</span></label>
        <div class="col-sm-9">
            <input type="text" name="identite" id="resa_add_identite" class="form-control" placeholder="Entrez un nom">
        </div>
    </div>
    <div class="form-group">
        <label for="prestation" class="col-sm-3 control-label">Activité <span class="mandatory">*</span></label>
        <div class="col-sm-9">
           <select name="prestation" id="prestation" class="form-control" onChange="calculTarif()">
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
        <label for="date_resa" class="col-sm-3 control-label">Date <span class="mandatory">*</span></label>
        <div class="col-sm-9"><input type="date" class="form-control" name="date_resa" id="date_resa" onChange="calculTarif()"></div>
    </div>
    <div class="form-group">
        <fieldset>
            <label for="heure_debut" class="col-sm-3 control-label">Début à <span class="mandatory">*</span></label>
            <div class="col-sm-9"><input type="time" class="form-control" id="heure_debut" name="heure_debut" onChange="calculTarif()"></div>
            <label for="heure_fin" class="col-sm-3 control-label">Fin à <span class="mandatory">*</span></label>
            <div class="col-sm-9"><input type="time" class="form-control" id="heure_fin" name="heure_fin" onChange="calculTarif()"></div>
        </fieldset>
    </div>
    <div class="form-group">
        <label for="lieu" class="col-sm-3 control-label">Salle <span class="mandatory">*</span></label>
        <div class="col-sm-9">
           <select name="lieu" class="form-control" id="lieu" onChange="calculTarif()">
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
    <div class="align-right">
    	<p class="" id="error_message"></p>
    <p id="prix_resa"> Prix de la réservation  : <span id="prix_calcul"></span></p>
    <input type="submit" name="addResa" value="Valider" class="btn btn-default btn-primary confirmAdd">
    </div>
</form>
</div>