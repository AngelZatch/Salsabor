<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
/** Ensemble de code pour ajouter un tarif pour une réservation **/
?>
<form action="tarifs_liste.php" method="post" class="form-horizontal" role="form">
    <div class="form-group">
        <label for="type_prestation" class="col-sm-3 control-label">Type de prestation <span class="span-mandatory">*</span></label>
        <div class="col-sm-9">
            <select name="type_prestation" class="form-control">
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
        <label for="jour" class="col-sm-3 control-label">Jours de réservation <span class="span-mandatory">*</span></label>
        <div class="col-sm-9">
            <input type="checkbox" name="jour-1" id="jour-1" class="checkbox-inline" value="1" onClick="toggleWeekHours()">Semaine
            <input type="checkbox" name="jour-2" id="jour-2" class="checkbox-inline" value="2">Samedi
            <input type="checkbox" name="jour-3" id="jour-3" class="checkbox-inline" value="3">Dimanche
        </div>
    </div>
    <div class="form-group" id="week-hours" style="display:none;">
    <label for="heures_semaine" class="col-sm-3 control-label">Plages horaires</label>
    <div class="col-sm-9">
       <?php
        $liste_plages = $db->query('SELECT * FROM plages_reservations WHERE plages_resa_jour=1');
        while($row_liste_plages = $liste_plages->fetch(PDO::FETCH_ASSOC)){
            echo "<input type='checkbox' class='checkbox-inline' value=".$row_liste_plages['plages_resa_id']." name=plage-".$row_liste_plages['plages_resa_id'].">".$row_liste_plages['plage_resa_nom']."</input>";
        }
        ?>
    </div>
    </div>
    <div class="form-group">
        <label for="lieu_resa" class="col-sm-3 control-label">Lieu réservé <span class="span-mandatory">*</span></label>
        <div class="col-sm-9">
            <?php
            $lieux = $db->query('SELECT * FROM salle WHERE est_salle_cours=1');
            while($row_lieux = $lieux->fetch(PDO::FETCH_ASSOC)){
                echo "<input type='checkbox' class='checkbox-inline' value=".$row_lieux['salle_id']." name='salle-".$row_lieux['salle_id']."'>".$row_lieux['salle_name'];
            }
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="prix_resa" class="col-sm-3 control-label">Prix <span class="span-mandatory">*</span></label>
        <div class="col-sm-9 input-group">
            <input type="text" class="form-control" name="prix_resa"><span class="input-group-addon">€</span>
        </div>
    </div>
    <input type="submit" name="addTarifResa" value="Ajouter" class="btn btn-default">
</form>