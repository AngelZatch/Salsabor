<?php
require_once "../functions/db_connect.php";
if(!isset($_POST['addStaff'])){
    addStaff();
}

function addStaff(){
    if(isset($_POST['prenom']) && (isset($_POST['nom'])) && (isset($_POST['rank']))){
        $prenom = $_POST['prenom'];
        $nom = $_POST['nom'];
        $rank = $_POST['rank'];
        //$db NULL
        $insertStaff = $db->prepare('INSERT INTO staff (prenom, nom, rank_id) VALUES(:prenom,:nom,:rank)');
        $insertStaff->execute(array(":prenom" => $prenom,
            ":nom" => $nom,
            ":rank" => $rank));
    }
    //$insertStaff->closeCursor();
}
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
            <input type="submit" name="addStaff" value="Ajouter" class="btn btn-default">
        </div>
    </form>
</div>