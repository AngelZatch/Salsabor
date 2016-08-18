<?php
class PDOFactory{
	public static function getConnection(){
		$db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', 'GztXCDj5A3UEDXGe');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $db;
	}
}
setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
?>
