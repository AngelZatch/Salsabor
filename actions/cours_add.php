<?php
require_once "../functions/db_connect.php";
/** Ensemble de code pour ajouter un cours. Cette page est appelée dans une lightbox dans cours_liste.php **/
?>
   <form action="cours_liste.php" method="post" class="form-horizontal" role="form">
        <div class="form-group">
            <label for="intitule" class="col-sm-3 control-label">Intitulé <span class="mandatory">*</span></label>
            <?php
            $autocomplete_cours_name = $db->query('SELECT intitule FROM cours');
            $return_arr = array();
            while($row_autocomplete_cours_name = $autocomplete_cours_name->fetch(PDO::FETCH_ASSOC)){
                array_push($return_arr, trim(preg_replace('/[0-9]+/', '', $row_autocomplete_cours_name['intitule'])));
            }
            ?>
            <div class="col-sm-9 ui-widget">
                <input type="text" class="form-control" name="intitule" id="cours_tags" placeholder="Nom du cours">
                <script>
                $(function(){
                    var coursNameTags = JSON.parse('<?php echo json_encode($return_arr);?>');
                    $('#cours_tags').autocomplete({
                        source: coursNameTags
                    });
                });
                </script>
            </div>
       </div>
       <div class="form-group">
            <label for="jour" class="col-sm-3 control-label">Jour <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" name="jour">
                <?php
                $jours = $db->query('SHOW COLUMNS FROM cours WHERE field="jours"');
                $row_jours = $jours->fetch(PDO::FETCH_ASSOC);
                foreach(explode("','",substr($row_jours['Type'],6,-2)) as $option){
                    echo "<option>$option</option>";
                }
                    ?>
                </select>
            </div>
       </div>
       <div class="form-group">
           <fieldset>
           <label for="herue_debut" class="col-sm-3 control-label">Début à <span class="mandatory">*</span></label>
           <div class="col-sm-9">
               <input type="time" class="form-control hasTimepicker" id="timepicker_locale_debut" name="heure_debut" placeholder="18h30">
           </div>
           <label for="heure_fin" class="col-sm-3 control-label">Fin à <span class="mandatory">*</span></label>
           <div class="col-sm-9">
               <input type="time" class="form-control hasTimepicker" id="timepicker_locale_fin" name="heure_fin" placeholder="19h30">
           </div>
           </fieldset>
       </div>
       <div class="form-group">
            <fieldset>
            <label for="prof_principal" class="col-sm-3 control-label">Professeur <span class="mandatory">*</span></label>
            <div class="col-sm-9">
               <select name="prof_principal" class="form-control">
                   <?php
                    $profs = $db->prepare('SELECT * FROM staff WHERE rank_id_foreign="1" OR rank_id_foreign="5"');
                    $profs->setFetchMode(PDO::FETCH_ASSOC);
                    $profs->execute();

                    $row_profs = $profs->fetchAll();
                    foreach ($row_profs as $r){
                        echo "<option value=".$r['staff_id'].">".$r['prenom']." ".$r['nom']."</option>";
                    }
                   ?>
               </select>
            </div>
            <label for="prof_remplacant" class="col-sm-3 control-label">Remplaçant <span class="mandatory">*</span></label>
            <div class="col-sm-9">
               <select name="prof_remplacant" class="form-control">
                   <?php
                    foreach ($row_profs as $r){
                        echo "<option value=".$r['staff_id'].">".$r['prenom']." ".$r['nom']."</option>";
                    }
                   ?>
               </select>
            </div>
            </fieldset>
       </div>
       <div class="form-group">
           <label for="niveau" class="col-sm-3 control-label">Niveau<span class="mandatory">*</span></label>
           <div class="col-sm-9">
           <select name="niveau" class="form-control">
           <?php
            $niveaux = $db->query('SELECT * FROM niveau');
            while($row_niveaux = $niveaux->fetch(PDO::FETCH_ASSOC)){
                echo "<option value=".$row_niveaux['niveau_id'].">".$row_niveaux['niveau_name']."</option>";
            }
            $niveaux->closeCursor();
           ?>
           </select>
           </div>
       </div>
       <div class="form-group">
           <label for="lieu" class="col-sm-3 control-label">Lieu<span class="mandatory">*</span></label>
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
       <div class="form-group">
           <label for="date_debut" class="col-sm-3 control-label">Date de Début<span class="mandatory">*</span></label>
           <div class="col-sm-9"><input type="date" class="form-control" name="date_debut"></div>
           <label for="date_fin" class="col-sm-3 control-label">Date de Fin<span class="mandatory">*</span></label>
           <div class="col-sm-9"><input type="date" class="form-control" name="date_fin"></div>
       </div>
       <div class="form-group">
           <label for="unite" class="col-sm-3 control-label">Unités<span class="mandatory">*</span></label>
           <div class="col-sm-9"><input type="text" class="form-control" name="unite" placeholder="Unités"></div>
       </div>
       <div class="form-group">
           <label for="cout_horaire" class="col-sm-3 control-label">Coût horaire<span class="mandatory">*</span></label>
           <div class="col-sm-9"><input type="text" class="form-control" name="cout_horaire" placeholder="Coût Horaire"></div>
       </div>
            <input type="submit" name="addCours" value="Ajouter" class="btn btn-default">
        </div>
    </form>