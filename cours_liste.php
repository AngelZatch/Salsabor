<?php
require_once "functions/db_connect.php";
require_once "functions/cours.php";
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Salsabor - Cours</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-cd"></span> Cours</h1>
               <div class="btn-toolbar"><a href="" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Ajouter un cours</a></div>
               <div class="menu-bar">
                   <ul class="nav nav-pills" id="tri-cours">
                       <li role="presentation" class="active"><a href=""><span class="glyphicon glyphicon-list"></span> Liste</a></li>
                       <li role="presentation"><a href=""><span class="glyphicon glyphicon-calendar"></span> Planning</a></li>
                   </ul>
               </div> <!-- menu-bar -->
               <br><br>
               <div class="table-responsive">
                   <table class="table table-striped table-hover">
                       <thead>
                           <tr>
                               <th class="col-sm-2">Intitulé</th>
                               <th class="col-sm-2">Jour</th>
                               <th class="col-sm-2">Professeur</th>
                               <th class="col-sm-2">Niveau</th>
                               <th class="col-sm-2">Lieu</th>
                               <th class="col-sm-2">Actions</th>
                           </tr>
                       </thead>
                       <tbody>
                       <?php
                        $cours = $db->query('SELECT * FROM cours JOIN staff ON (prof_principal=staff.staff_id) JOIN niveau ON(niveau=niveau.niveau_id) JOIN salle ON (salle=salle.salle_id)');
                        while($row_cours = $cours->fetch(PDO::FETCH_ASSOC)){
                            echo "<tr>
                            <td class='col-sm-2'>".$row_cours['intitule']."</td>
                            <td class='col-sm-2'>".$row_cours['jours']."<br>".$row_cours['heure_debut']." - ".$row_cours['heure_fin']."</td>
                            <td class='col-sm-2'>".$row_cours['prenom']." ".$row_cours['nom']."</td>
                            <td class='col-sm-2'><span class='label label-level-".$row_cours['niveau_id']."'>".$row_cours['niveau_name']."</span></td>
                            <td class='col-sm-2'>".$row_cours['salle_name']."</td>
                            <td class='col-sm-2'>
                            <form method='post'>
                            <div class='btn btn-group' role='group'>
                                <button type='submit' class='btn btn-default'><span class='glyphicon glyphicon-edit'></span></button>
                                <button type='submit' class='btn btn-default'><span class='glyphicon glyphicon-trash'></span></button>
                            </div>
                            <input type='hidden' name='id' value=".$row_cours['cours_id'].">
                            </form>
                            </td>
                            </tr>";
                        };
                        ?>
                       </tbody>
                   </table>
               </div> <!-- table-responsive -->
           </div> <!-- col-sm-10 main -->
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>