<?php
$db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '', array(PDO::ATTR_PERSISTENT => true));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
?>