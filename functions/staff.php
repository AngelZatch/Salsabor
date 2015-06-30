<?php
require_once "db_connect.php";
/** ADD STAFF **/
function addStaff(){
    $db = PDOFactory::getConnection();
    $insertStaff = $db->prepare('INSERT INTO staff(prenom, nom, date_naissance, date_inscription, rue, code_postal, ville, mail, tel_fixe, tel_port, rank_id_foreign) VALUE(:prenom, :nom, :date_naissance, :date_inscription, :rue, :code_postal, :ville, :mail, :tel_fixe, :tel_port, :rank)');
    $insertStaff->execute(array(":prenom" => $_POST['prenom'],
                                ":nom" => $_POST['nom'],
                                ":date_naissance" => $_POST['date_naissance'],
                                ":date_inscription" => date('Y-m-d', time()),
                                ":rue" => $_POST['rue'],
                                ":code_postal" => $_POST['code_postal'],
                                ":ville" => $_POST['ville'],
                                ":mail" => $_POST['mail'],
                                ":tel_fixe" => $_POST['tel_fixe'],
                                ":tel_port" => $_POST['tel_port'],
                                ":rank" => $_POST['rank']));
}

/** EDIT STAFF **/
function editStaff(){
    $db = PDOFactory::getConnection();
}

/** DELETE STAFF **/
function deleteStaff(){
    $db = PDOFactory::getConnection();
    $deleteStaff = $db->prepare('DELETE FROM staff WHERE staff_id=?');
    $deleteStaff->bindValue(1,$_POST['id'], PDO::PARAM_INT);
    $deleteStaff->execute();
}

/** ADD RANK **/
function addRank(){
    $db = PDOFactory::getConnection();
    $insertRank = $db->prepare('INSERT INTO rank (rank_name) VALUES(?)');
    $insertRank->bindValue(1,$_POST['rank_name'],PDO::PARAM_STR);
    $insertRank->execute();
}

/** DELETE RANK **/
function deleteRank(){
    $db = PDOFactory::getConnection();
    $deleteRank = $db->prepare('DELETE FROM rank WHERE rank_id=?');
    $deleteRank->bindValue(1, $_POST['id'], PDO::PARAM_INT);
    $deleteRank->execute();
}
?>