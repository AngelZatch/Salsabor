<?php
include "functions/db_connect.php";
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
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>