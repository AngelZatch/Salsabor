<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

// Forfaits possiblement modifiés par l'ajout d'un jour chômé
$forfaits = $db->query("SELECT * FROM produits_adherents")->fetchAll(PDO::FETCH_ASSOC);
$updated = array();

$queryHoliday = $db->prepare("SELECT date_chomee FROM jours_chomes WHERE jour_chome_id=?");
$queryHoliday->bindParam(1, $_POST["delete_id"]);
$queryHoliday->execute();
$holiday = $queryHoliday->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	$delete = $db->prepare('DELETE FROM jours_chomes WHERE jour_chome_id=?');
	$delete->bindParam(1, $_POST["delete_id"]);
	$delete->execute();

	foreach ($forfaits as $row => $forfait){
		if($forfait["date_activation"] <= $holiday["date_chomee"] && $forfait["date_expiration"] >= $holiday["date_chomee"]){
			$u = array();
			$new_exp_date = date("Y-m-d 00:00:00",strtotime($forfait["date_expiration"].'-1DAYS'));
			$u["id"] = $forfait["id_transaction_foreign"];
			$u["old_date"] = $forfait["date_expiration"];
			$u["new_date"] = $new_exp_date;
			$update = $db->prepare("UPDATE produits_adherents SET date_expiration =:date_expiration WHERE id_transaction_foreign=:id");
			$update->bindParam(':date_expiration', $new_exp_date);
			$update->bindParam(':id', $forfait["id_transaction_foreign"]);
			$update->execute();
			array_push($updated, $u);
		}
	}
	$db->commit();
	echo json_encode($updated);
} catch (PDOExecption $e) {
	$db->rollBack();
	$message = var_dump($e->getMessage());
	$data = array('type' => 'error', 'message' => ' '.$message);
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
}
?>
