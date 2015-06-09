<?php
require_once "../functions/db_connect.php";
/** Ensemble de code pour ajouter un cours. Cette page est appelée dans une lightbox dans cours_liste.php **/
?>
   <form action="cours_liste.php" method="post" class="form-horizontal" role="form">
        <div class="form-group">
            <label for="intitule" class="col-sm-3 control-label">Intitulé <span class="mandatory">*</span></label>
            <?php
            $cours_name = $db->query('SELECT DISTINCT cours_intitule FROM cours');
            $arr_cours_name = array();
            while($row_cours_name = $cours_name->fetch(PDO::FETCH_ASSOC)){
                array_push($arr_cours_name, trim(preg_replace('/[0-9]+/', '', $row_cours_name['cours_intitule'])));
            }
            ?>
            <div class="col-sm-9 ui-widget">
                <input type="text" class="form-control" name="intitule" id="cours_tags" placeholder="Nom du cours">
                <script>
                $(function(){
                    var coursNameTags = JSON.parse('<?php echo json_encode($arr_cours_name);?>');
                    $('#cours_tags').autocomplete({
                        source: coursNameTags
                    });
                });
                </script>
            </div>
       </div>
      <div class="form-group">
           <label for="date_debut" class="col-sm-3 control-label">Date de Début<span class="mandatory">*</span></label>
           <div class="col-sm-9"><input type="date" class="form-control" name="date_debut"></div>
           
           <div class="col-sm-9 col-sm-offset-3">
               <label for="recurrence" class="control-label"><input type="checkbox" name="recurrence" id="recurrence" class="checkbox-inline" value="1" onClick="toggleRecurringOptions()">Est récurent<span class="mandatory">*</span></label>
           </div>
       </div>
      <div class="form-group" id="recurring-options" style="display:none;">
           <label for="date_fin" class="col-sm-3 control-label">Date de Fin<span class="mandatory">*</span></label>
           <div class="col-sm-9">
               <input type="date" class="form-control" name="date_fin">
          </div>
              <label for="frequence_repetition" class="col-sm-3 control-label">Récurrence<span class="mandatory">*</span></label>
               <div class="col-sm-9">
                   <div id="options-recurrence">
                       <input type="radio" value="1" name="frequence_repetition"> Quotidienne<br>
                       <input type="radio" value="7" name="frequence_repetition"> Hebdomadaire <br>
                       <input type="radio" value="14" name="frequence_repetition"> Bi-mensuelle<br>
                   </div>
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
            $row_niveaux = $niveaux->fetch(PDO::FETCH_ASSOC);
            while($row_niveaux = $niveaux->fetch(PDO::FETCH_ASSOC)){
                echo"<option value=".$row_niveaux['niveau_id'].">".$row_niveaux['niveau_name']."</option>";
            }
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
            <input type="submit" name="addCours" value="Ajouter" class="btn btn-default">
        </div>
    </form>