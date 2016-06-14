<?php
// This is the generic script to edit an entry into whatever table. With this, you only have to call it once you're set and avoid a billion scripts for every little thing.
require_once "db_connect.php";
require_once "tools.php";
$db = PDOFactory::getConnection();

// Array of values (user serialize in php to have the correct format)
parse_str($_POST["values"], $values);

// The table and entry of it we'll update
$table_name = $_POST["table_name"];
$entry_id = $_POST["entry_id"];

// We get the name of the primary key
$primary_key = $db->query("SHOW INDEX FROM $table_name WHERE Key_name = 'PRIMARY'")->fetch(PDO::FETCH_ASSOC);

// Construction of the query
$query = "UPDATE $table_name SET ";
foreach($values as $row => $value){
	// Have to solve users to their ID if needed here.
	if($row == "prof_principal"){
		$value = solveAdherentToId($value);
	}
	$value = htmlspecialchars($value);
	$query .= "$row = '$value'";
	if($row !== end(array_keys($values))){
		$query .= ", ";
	}
}
$query .= " WHERE $primary_key[Column_name] = '$entry_id'";

// Execution
try{
	$db->beginTransaction();
	$update = $db->query($query);
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
