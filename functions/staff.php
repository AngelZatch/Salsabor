<?php
/** ADD STAFF **/
function addStaff(){
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $insertStaff = $db->prepare('INSERT INTO staff (prenom, nom, rank_id) VALUES(:prenom,:nom,:rank)');
    $insertStaff->execute(array(":prenom" => $_POST['prenom'],
        ":nom" => $_POST['nom'],
        ":rank" => $_POST['rank']));
}

/** EDIT STAFF **/
function editStaff(){
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
}

/** DELETE STAFF **/
function deleteStaff(){
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $deleteStaff = $db->prepare('DELETE FROM staff WHERE id=?');
    $deleteStaff->bindValue(1,$_POST['id'], PDO::PARAM_INT);
    $deleteStaff->execute();
}

/** ADD RANK **/
function addRank(){
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $insertRank = $db->prepare('INSERT INTO rank (rank_name) VALUES(?)');
    $insertRank->bindValue(1,$_POST['rank_name'],PDO::PARAM_STR);
    $insertRank->execute();
}

/** DELETE RANK **/
function deleteRank(){
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $deleteRank = $db->prepare('DELETE FROM rank WHERE id=?');
    $deleteRank->bindValue(1, $_POST['id'], PDO::PARAM_INT);
    $deleteRank->execute();
}
?>