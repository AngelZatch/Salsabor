<?php
require_once "db_connect.php";
require_once "tools.php";
$db = PDOFactory::getConnection();

parse_str($_POST["values"], $values);

$table_name = htmlspecialchars($_POST["table"]);

// Constructing generic query
$query = "INSERT INTO $table_name(";
foreach($values as $column => $value){
	$query .= "$column";
	if($column !== end(array_keys($values))){
		$query .= ", ";
	} else {
		$query .= ")";
	}
}
$query .= " VALUES(";
foreach($values as $column => $value){
	if($column == "session_teacher" || $column == "event_handler"){
		$value = solveAdherentToId($value);
	}
	$value = htmlspecialchars($value);
	$query .= "'$value'";
	if($column !== end(array_keys($values))){
		$query .= ", ";
	} else {
		$query .= ")";
	}
}

try{
	$db->beginTransaction();
	$insert = $db->query($query);
	echo $db->lastInsertId();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
