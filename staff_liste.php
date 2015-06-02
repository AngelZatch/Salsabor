<?php
require_once "functions/db_connect.php";
$rank_value = $_GET['rank'];
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
               <h1>Liste du staff</h1>
               <div class="btn-toolbar">
                   <a href="actions/staff_add.php" role="button" class="btn btn-default" data-title="Ajouter un staff" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un staff</a>
                   <a href="" role="button" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Ajouter un rang</a>
                   <a href="" role="button" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span> Modifier un rang</a>
               </div>
               <br>
               <ul class="nav nav-tabs">
                 <?php $staff_ranks = $db->query('SELECT * FROM rank');
                    if($rank_value == 0){
                        echo "<li role='presentation' class='active'><a href='".$_SERVER['PHP_SELF']."?rank=0'>Tous</a></li>";
                    } else {
                        echo "<li role='presentation'><a href='".$_SERVER['PHP_SELF']."?rank=0'>Tous</a></li>";
                    }
                    while($row_staff_ranks = $staff_ranks->fetch(PDO::FETCH_ASSOC)){
                        if($rank_value == $row_staff_ranks['id']){
                            echo "<li role='presentation' class='active'><a href=".$_SERVER['PHP_SELF']."?rank=".$row_staff_ranks['id'].">".$row_staff_ranks['rank_name']."</a></li>";
                        } else {
                        echo "<li role='presentation'><a href=".$_SERVER['PHP_SELF']."?rank=".$row_staff_ranks['id'].">".$row_staff_ranks['rank_name']."</a></li>";
                        }
                    }?>
               </ul>
               <div class="table-responsive">
                   <table class="table table-striped table-hover">
                       <thead>
                           <tr>
                               <th class="col-sm-3">Prénom</th>
                               <th class="col-sm-3">Nom</th>
                               <th class="col-sm-2">Rang</th>
                               <th class="col-sm-4">Actions</th>
                           </tr>
                       </thead>
                       <tbody>
                           <tr>
                               <?php if($rank_value==0) $staff_members = $db->query('SELECT * FROM staff');
                                        else {
                                            $staff_members = $db->prepare('SELECT * FROM staff WHERE id=?');
                                            $staff_members->bindParam(1, $rank_value, PDO::PARAM_INT);
                                            $staff_members->execute();
                                        }
                                while($row_staff_members = $staff_members->fetch(PDO::FETCH_ASSOC)){
                                    echo "<td class='col-sm-3'>".$row_staff_members['prenom']."</td>
                                        <td class='col-sm-3'>".$row_staff_members['nom']."</td>
                                        <td class='col-sm-2'>";
                                    $member_rank = $db->prepare('SELECT * FROM rank JOIN staff ON(rank_id=rank.id) WHERE nom=?');
                                    $member_rank->bindParam(1, $row_staff_members['nom'], PDO::PARAM_STR);
                                    $member_rank->execute();
                                    while($row_member_rank = $member_rank->fetch(PDO::FETCH_ASSOC)){
                                        echo $row_member_rank['rank_name']."</td>";
                                    }
                                    echo "<td class='col-sm-4'>
                                    <div class='btn-group' role='group'>
                                    <a href='' type='button' role='button' class='btn btn-default'><span class='glyphicon glyphicon-edit'></span> Modifier</a>
                                    <a href='' type='button' role='button' class='btn btn-default'><span class='glyphicon glyphicon-trash'></span> Supprimer</a>
                                    </div>
                                    </td>";
                                    
                                };
                               ?>
                           </tr>
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
    <script>
        $(document).ready(function ($) {
            // delegate calls to data-toggle="lightbox"
            $(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
                event.preventDefault();
                return $(this).ekkoLightbox({
                    onNavigate: false
                });
            });
        });
    </script>
</body>
</html>