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
               <h1 class="page-title"><span class="glyphicon glyphicon-blackboard"></span> Base Professeurs</h1>
               <div class="btn-toolbar">
                   <a href="" role="button" class="btn btn-primary disabled"><span class="glyphicon glyphicon-plus"></span> Ajouter un professeur</a>
               </div> <!-- btn-toolbar -->
               <?php
                $profs = $db->query('SELECT * FROM professeurs');
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="col-sm-4">Professeur</th>
                                <th class="col-sm-5"></th>
                                <th class="col-sm-3"></th>
                            </tr>
                        </thead>
                        <tbody id="filter-enabled">
                            <?php
                            while($row_profs = $profs->fetch(PDO::FETCH_ASSOC)){
                            ?>
                            <tr>
                                <td class="col-sm-4">
                                <?php
                                    echo $row_profs['prenom'].' '.$row_profs['nom'];
                                ?>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>