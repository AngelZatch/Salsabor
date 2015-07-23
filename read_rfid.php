<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

if(isset($_GET["carte"])){
    $data = explode('*', $_GET["carte"]);
    $tag_rfid = $data[0];
    $ip_rfid = $data[1];
}

if(isset($_POST["add"])){
    $new = $db->prepare("INSERT INTO passages(passage_eleve, passage_salle, passage_date)
    VALUE(:tag, :salle, :date)");
    $new->bindParam(':tag', $_POST["tag"]);
    $new->bindParam(':salle', $_POST["salle"]);
    $new->bindParam(':date', date_create('now')->format('Y-m-d H:i:s'));
    $new->execute();
    
    header('Location: passages.php');
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Template - Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-qrcode"></span> RFID</h1>
               <p>Simulez un passage RFID</p>
               <form action="" method="post">
                  <label for="tag">Tag</label>
                   <input type="text" name="tag" class="form-control">
                   
                   <label for="salle">Salle du lecteur</label>
                   <input type="text" name="salle" class="form-control">
                   
                   <input type="submit" value="SIMULER UN PASSAGE" name="add" class="btn btn-primary confirm-add">
               </form>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>