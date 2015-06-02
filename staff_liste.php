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
           <h1>Liste du staff</h1>
           <div class="btn-toolbar"><a href="" role="button" class="btn btn-default"><span class="glyphicon glyphicon-plus"> Ajouter un staff</span></a></div>
           <ul class="nav nav-tabs">
             <?php $staff_ranks = $db->query('SELECT * FROM rank');
                while($row_staff_ranks = $staff_ranks->fetch(PDO::FETCH_ASSOC)){
                    echo "<li role='presentation'><a href=''>".$row_staff_ranks['rank_name']."</a></li>";
                }?>
           </ul>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>