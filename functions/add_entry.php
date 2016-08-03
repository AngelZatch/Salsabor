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
	if($column == "session_teacher" || $column == "event_handler" || $column == "booking_holder"){
		$value = solveAdherentToId($value);
	}
	if(preg_match("/(start|end|date)/i", $column)){
		// In the database, all dates contain one of these 3 words. We can then test against them to find dates and format them correctly.
		$value_date = DateTime::createFromFormat("d/m/Y H:i:s", $value);
		$value = $value_date->format("Y-m-d H:i:s");
	} else {
		$value = htmlspecialchars($value);
	}
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
