<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryHolidays = $db->query("SELECT * FROM jours_chomes");
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Jours Chômés | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
				<div class="btn-toolbar" id="top-page-buttons">
                   <a href="planning.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à la liste des adhérents</a>
                </div> <!-- btn-toolbar -->
               <h1 class="page-title"><span class="glyphicon glyphicon-leaf"></span> Jours Chômés</h1>
               <table class="table table-striped">
                   <thead>
                       <tr>
                           <th>Jour chômé</th>
                           <th></th>
                       </tr>
                   </thead>
                   <tbody>
                       <?php while($holidays = $queryHolidays->fetch(PDO::FETCH_ASSOC)){ ?>
                       <tr>
                           <td><?php echo date_create($holidays["date_chomee"])->format('d/m/Y');?></td>
                           <td><button class="btn btn-default"><span class="glyphicon glyphicon-trash"></span> Supprimer</button></td>
                       </tr>
                       <?php } ?>
                   </tbody>
               </table>
               <button class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Ajouter un jour / une période chômé(e)</button>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>