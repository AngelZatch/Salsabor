<?php
require_once 'functions/db_connect.php';
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
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-map-marker"></span> Passages</h1>
               <p id="last-edit">Il y a actuellement <?php echo $nombrePassages;?> cours à venir ou déjà en cours.</p>
               <?php while($passages = $queryPassages->fetch(PDO::FETCH_ASSOC)){ ?>
               <p>Passage : <?php echo $passages["passage_date"];?></p>
               <?php } ?>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>