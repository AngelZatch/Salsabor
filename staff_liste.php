<?php
require_once "functions/db_connect.php";
/** Le fichier functions/staff.php contient toutes les fonctions relatives au staff.**/
require_once "functions/staff.php";

/** Fetch du rank pour filtrer rapidement les personnes par pouvoir administratif **/
$rank_value = $_GET['rank'];

/** Chaque trigger de tous les formulaires appelle une des fonctions dans functions/staff.php **/
// Ajout de staff
if(isset($_POST['addStaff'])){
    addStaff();
}

// Suppression d'un staff
if(isset($_POST['deleteStaff'])){
    deleteStaff();
}

// Ajout d'un rang administratif
if(isset($_POST['addRank'])){
    addRank();
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
               <h1 class="page-title"><span class="glyphicon glyphicon-briefcase"></span> Gestion du staff</h1>
               <div class="btn-toolbar">
                   <a href="actions/staff_add.php" role="button" class="btn btn-primary" data-title="Ajouter un staff" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un staff</a>
                   <a href="actions/rank_add.php" role="button" class="btn btn-primary" data-title="Ajouter un rang" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un rang</a>
               </div>
               <br>
               <ul class="nav nav-tabs">
                 <?php 
                    /** Fetch des ranks et affichage dans l'URL. Pour tout afficher, rank vaut 0. **/
                    $staff_ranks = $db->query('SELECT * FROM rank');
                    if($rank_value == 0){
                        echo "<li role='presentation' class='active'><a href='".$_SERVER['PHP_SELF']."?rank=0'>Tous</a></li>";
                    } else {
                        echo "<li role='presentation'><a href='".$_SERVER['PHP_SELF']."?rank=0'>Tous</a></li>";
                    }
                    /** Construction des options de filtrage **/
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
                               <th class="col-sm-2">Identité</th>
                               <th class="col-sm-1">Inscrit le</th>
                               <th class="col-sm-2">Adresse postale</th>
                               <th class="col-sm-2">Contact</th>
                               <th class="col-sm-1">Rang</th>
                               <th class="col-sm-2">Actions</th>
                           </tr>
                       </thead>
                       <tbody>
                       <?php
                        /** Affichage de tous les membres du staff possédant le rang administratif correspondant **/
                        if($rank_value==0) $staff_members = $db->query('SELECT * FROM staff');
                                else {
                                    $staff_members = $db->prepare('SELECT * FROM staff WHERE rank_id=?');
                                    $staff_members->bindParam(1, $rank_value, PDO::PARAM_INT);
                                    $staff_members->execute();
                                }
                        while($row_staff_members = $staff_members->fetch(PDO::FETCH_ASSOC)){
                            echo "<tr>
                                <td class='col-sm-2'>".$row_staff_members['prenom']." ".$row_staff_members['nom']."</td>
                                <td class='col-sm-1'>".(date_create($row_staff_members['date_inscription'])->format('j M y'))."</td>
                                <td class='col-sm-2'>".$row_staff_members['rue']."<br>"." ".$row_staff_members['code_postal']." ".$row_staff_members['ville']."</td>
                                <td class='col-sm-2'>".$row_staff_members['mail']."<br>".$row_staff_members['tel_fixe']." / ".$row_staff_members['tel_port']."</td>
                                <td class='col-sm-2'>";
                            $member_rank = $db->prepare('SELECT * FROM rank JOIN staff ON(rank_id=rank.id) WHERE nom=?');
                            $member_rank->bindParam(1, $row_staff_members['nom'], PDO::PARAM_STR);
                            $member_rank->execute();
                            while($row_member_rank = $member_rank->fetch(PDO::FETCH_ASSOC)){
                                echo "<span class='label label-rank-".$row_member_rank['rank_id']."'>".$row_member_rank['rank_name']."</span></td>";
                            }
                            echo "<td class='col-sm-1'>
                            <form method='post' action='staff_liste.php?rank=0'>
                            <div class='btn-group' role='group'>
                                <a href='actions/staff_edit.php&nom=".$row_staff_members['nom']."' type='button' role='button' class='btn btn-default'><span class='glyphicon glyphicon-edit'></span></a>
                                 <button type='submit' class='btn btn-default'><span class='glyphicon glyphicon-send'></span></button>
                                <button type='submit' name='deleteStaff' class='btn btn-default'><span class='glyphicon glyphicon-trash'></span></button>
                            </div>
                            <input type='hidden' name='id' value=".$row_staff_members['id'].">
                            </form>
                            </td>
                            </tr>";
                        };
                       ?>
                       </tbody>
                   </table>
               </div> <!-- table-responsive -->
               <!--
               <div class="btn-toolbar">
                   <button class="btn btn-default"><span class="glyphicon glyphicon-edit"></span> Modifier le rang</button>
                   <button class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Supprimer le rang</button>
               </div> -->
           </div> <!-- col-sm-10 main-->
       </div> <!-- row -->
   </div> <!-- container-fluid -->
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