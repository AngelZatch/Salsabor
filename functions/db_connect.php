<?php
class PDOFactory{
	public static function getConnection(){
		$db = new PDO('mysql:host=127.0.0.1;dbname=Salsabor;charset=utf8', 'root', 'GztXCDj5A3UEDXGe');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $db;
	}
}
setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
?>
