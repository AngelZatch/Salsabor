<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
/** Ensemble de code pour ajouter un staff. Cette page est appelée dans une lightbox dans staff_liste.php **/
?>
   <form action="staff_liste.php?rank=0" method="post" class="form-horizontal" role="form">
        <div class="form-group">
            <label for="prenom" class="col-sm-3 control-label">Nom <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="prenom" placeholder="Prénom">
            </div>
       </div>
       <div class="form-group">
            <label for="nom" class="col-sm-3 control-label">Prénom <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control col-sm-9" name="nom" placeholder="Nom">
            </div>
       </div>
       <div class="form-group">
            <label for="date_naissance" class="col-sm-3 control-label">Date de naissance <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <input type="date" class="form-control" name="date_naissance" placeholder="Date de naissance">
            </div>
       </div>
       <div class="form-group">
            <fieldset>
            <label for="rue" class="col-sm-3 control-label">Adresse <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="rue" placeholder="Rue">
            </div>
            <label for="code_postal" class="col-sm-3 control-label">Code Postal <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="code_postal" placeholder="Code Postal">
            </div>
            <label for="ville" class="col-sm-3 control-label">Ville <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="ville" placeholder="Ville">
            </div>
            </fieldset>
       </div>
       <div class="form-group">
           <label for="mail" class="col-sm-3 control-label">Adresse mail<span class="mandatory">*</span></label>
           <div class="col-sm-9"><input type="text" class="form-control" name="mail" placeholder="Adresse mail"></div>
       </div>
       <div class="form-group">
           <label for="tel_fixe" class="col-sm-3 control-label">Téléphone fixe<span class="mandatory">*</span></label>
           <div class="col-sm-9"><input type="text" class="form-control" name="tel_fixe" placeholder="Téléphone fixe"></div>
       </div>
       <div class="form-group">
           <label for="tel_port" class="col-sm-3 control-label">Téléphone portable</label>
           <div class="col-sm-9"><input type="text" class="form-control" name="tel_port" placeholder="Téléphone portable"></div>
       </div>
       <div class="form-group">
            <label for="rank" class="col-sm-3 control-label">Rang <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" name="rank">
                <?php
                $staff_ranks = $db->query('SELECT * FROM rank');
                while($row_staff_ranks = $staff_ranks->fetch(PDO::FETCH_ASSOC)){
                    echo "<option value=".$row_staff_ranks['rank_id'].">".$row_staff_ranks['rank_name']."</option>";
                }
                $staff_ranks->closeCursor();
                ?>
                </select>
            </div>
       </div>
            <input type="submit" name="addStaff" value="Ajouter" class="btn btn-default">
        </div>
    </form>