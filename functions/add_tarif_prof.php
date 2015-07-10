<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

try{
	$db->beginTransaction();
	$new = $db->prepare('INSERT INTO tarifs_professeurs(prof_id_foreign,type_prestation,tarif_prestation,ratio_multiplicatif)
	VALUES(:prof_id, :prestation, :tarif, :ratio)');
	$new->bindParam(':prof_id', $_POST["prof_id"]);
	$new->bindParam(':prestation', $_POST["prestation"]);
	$new->bindParam(':tarif', $_POST["tarif"]);
	$new->bindParam(':ratio', $_POST["ratio"]);
	$new->execute();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	$message = var_dump($e->getMessage());
	$data = array('type' => 'error', 'message' => ' '.$message);
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
}
?>