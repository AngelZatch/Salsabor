<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();

$data = explode(' ', $_POST["identite"]);
$prenom = $data[0];
$nom = '';
for($i = 1; $i < count($data); $i++){
	$nom .= $data[$i];
	if($i != count($data)){
		$nom .= " ";
	}
}

$search = $db->prepare('SELECT * FROM users WHERE user_prenom=? AND user_nom=?');
$search->bindParam(1, $prenom);
$search->bindParam(2, $nom);
$search->execute();
if($search->rowCount() == 1){
	$res = $search->fetch(PDO::FETCH_ASSOC);
	echo $res["user_id"];
} else {
	echo $search->rowCount();
}
?>
