<?php
require_once 'functions/db_connect.php';
$data = $_GET['id'];
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
               <h1 class="page-title"><span class="glyphicon glyphicon-pencil"></span> Modifier un cours</h1>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>