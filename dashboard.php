<?php
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
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
           <div class="col-sm-9 main">
           <h1><span class="glyphicon glyphicon-dashboard"></span> Panneau principal</h1>
           <a class="btn btn-primary btn-big" href="eleve_add.php">INSCRIRE UN NOUVEL ADHERENT</a>
           <a class="btn btn-primary btn-big" href="vente_forfait.php">REALISER UNE VENTE DE FORFAIT</a>
           <a class="btn btn-primary btn-big">LIRE UNE CARTE ADHERENT</a>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>