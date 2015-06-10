<?php
    $index = 38;
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    try{
        $db->beginTransaction();
        $findParent = $db->prepare('SELECT COUNT(*) FROM cours WHERE cours_parent_id=?');
        $findParent->bindParam(1, $index, PDO::PARAM_INT);
        $findParent->execute();
        print_r($findParent->fetchColumn());
    } catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
    /** Il faut vérifier si la table parent a encore d'autres entrées pour cet id dans la table cours. Si elle n'en a plus, alors il faut supprimer l'entrée parente également. **/
?>