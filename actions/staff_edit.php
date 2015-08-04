<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
/** Ensemble de code pour éditer un staff. Cette page est appelée dans une lightbox dans staff_liste.php **/
?>
<div class="row">
   <form action="staff_liste.php?rank=0" method="post" class="lightbox-form">
        <div class="form-group">
            <label for="prenom" class="control-label">Prénom</label>
            <input type="text" class="form-control" name="prenom" placeholder="Prénom">
            <br>
            <label for="nom" class="control-label">Nom</label>
            <input type="text" class="form-control" name="nom" placeholder="Nom">
            <br>
            <label for="rank" class="control-label">Rang</label>
            <select class="form-control" name="rank">
            <?php
            $staff_ranks = $db->query('SELECT * FROM rank');
            while($row_staff_ranks = $staff_ranks->fetch(PDO::FETCH_ASSOC)){
                echo "<option value=".$row_staff_ranks['id'].">".$row_staff_ranks['rank_name']."</option>";
            }
            $staff_ranks->closeCursor();
            ?>
            </select>
            <br>
            <input type="submit" name="addStaff" value="Ajouter" class="btn btn-primary">
        </div>
    </form>
</div>