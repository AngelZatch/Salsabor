<?php
class PDOFactory{
	public static function getConnection(){
		$db = new PDO('mysql:host=127.0.0.1;dbname=Salsabor_cutting_edge;charset=utf8', 'root', 'GztXCDj5A3UEDXGe');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $db;
	}
}
setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
?>
