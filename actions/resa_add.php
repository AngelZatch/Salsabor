<?php
require_once "../functions/db_connect.php";
?>
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
           <select name="prestation" id="" class="form-control">
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
        <div class="col-sm-9"><input type="date" class="form-control" name="date_resa"></div>
    </div>
    <div class="form-group">
        <fieldset>
            <label for="heure_debut" class="col-sm-3 control-label">Début à <span class="mandatory">*</span></label>
            <div class="col-sm-9"><input type="time" class="form-control" name="heure_debut" placeholder="19h30"></div>
            <label for="heure_fin" class="col-sm-3 control-label">Fin à <span class="mandatory">*</span></label>
            <div class="col-sm-9"><input type="time" class="form-control" name="heure_fin" placeholder="21h30"></div>
        </fieldset>
    </div>
    <div class="form-group">
        <label for="lieu" class="col-sm-3 control-label">Salle <span class="mandatory">*</span></label>
        <div class="col-sm-9">
           <select name="lieu" class="form-control">
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
    <input name="calculerTarif" value="Calculer le tarif" class="btn btn-default" id="calcul-tarif">
    <p id="prix_resa"> Prix de la réservation  : </p>
</form>