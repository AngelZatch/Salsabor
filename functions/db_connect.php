<?php
/*$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Salsabor";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if(!$conn){
    die("La connexion a échoué.".mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");*/

$db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
?>