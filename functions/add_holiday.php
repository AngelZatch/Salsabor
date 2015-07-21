<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$start = $_POST["start"];
if(isset($_POST["end"]) && $_POST["end"] != ""){
    $end = $_POST["end"];
    echo $end;
    $periode = ((strtotime($end) - strtotime($start))/86400)+1;
} else {
    $periode = 1;
}

try{
	$db->beginTransaction();
    for($i = 0; $i < $periode; $i++){
        $new = $db->prepare('INSERT INTO jours_chomes(date_chomee) VALUES(:date)');
        $new->bindParam(':date', $start);
        $new->execute();
        $start_date = strtotime($start.'+1DAYS');
        $start = date("Y-m-d", $start_date);
    }
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>