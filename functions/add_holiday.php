<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

// Ajout du jour chômé
$start = $_POST["start"];
if(isset($_POST["end"]) && $_POST["end"] != ""){
    $end = $_POST["end"];
    echo $end;
    $periode = ((strtotime($end) - strtotime($start))/86400)+1;
} else {
    $periode = 1;
}

// Forfaits possiblement modifiés par l'ajout d'un jour chômé
$forfaits = $db->query("SELECT * FROM produits_adherents")->fetchAll(PDO::FETCH_ASSOC);
$updated = array();

try{
	$db->beginTransaction();
    for($i = 0; $i < $periode; $i++){
        $new = $db->prepare('INSERT INTO jours_chomes(date_chomee) VALUES(:date)');
        $new->bindParam(':date', $start);
        $new->execute();
        // Détermination de l'impact sur les forfaits en cours
        foreach ($forfaits as $row => $forfait){
            if($forfait["date_activation"] <= $start && $forfait["date_expiration"] >= $start){
                $u = array();
                $new_exp_date = date("Y-m-d 00:00:00",strtotime($forfait["date_expiration"].'+1DAYS'));
                $u["id"] = $forfait["id_transaction"];
                $u["old_date"] = $forfait["date_expiration"];
                $u["new_date"] = $new_exp_date;
                $update = $db->prepare("UPDATE produits_adherents SET date_expiration =:date_expiration WHERE id_transaction=:id");
                $update->bindParam(':date_expiration', $new_exp_date);
                $update->bindParam(':id', $forfait["id_transaction"]);
                $update->execute();
                array_push($updated, $u);
            }
        }
        $start_date = strtotime($start.'+1DAYS');
        $start = date("Y-m-d", $start_date);
    }
	$db->commit();
    echo json_encode($updated);
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>