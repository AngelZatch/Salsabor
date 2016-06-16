<?php
require_once "db_connect.php";
require_once "cours.php";

$db = PDOFactory::getConnection();
$parent_id = $_GET["parent_id"];

checkParent($db, $parent_id);

function checkParent($db, $parent_id){
	try{
		$db->beginTransaction();
		$findParent = $db->prepare('SELECT COUNT(*) FROM cours WHERE cours_parent_id=?');
		$findParent->bindParam(1, $parent_id, PDO::PARAM_INT);
		$findParent->execute();
		if($findParent->fetchColumn() == 0){
			$deleteAll = $db->prepare('DELETE FROM cours_parent WHERE parent_id=?');
			$deleteAll->bindParam(1, $parent_id, PDO::PARAM_INT);
			$deleteAll->execute();
		}
		$db->commit();
	} catch(PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}

?>
