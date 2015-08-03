<?php
require_once "functions/db_connect.php";
$db = PDOFactory::getConnection();
/** Le fichier functions/staff.php contient toutes les fonctions relatives au staff.**/
require_once "functions/staff.php";

/** Fetch du rank pour filtrer rapidement les personnes par pouvoir administratif **/
$rank_value = $_GET['rank'];

$queryRanks = $db->query('SELECT * FROM rank');

/** Chaque trigger de tous les formulaires appelle une des fonctions dans functions/staff.php **/

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
    <title>Staff | Salsabor</title>
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
                   <a href="staff_add.php" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Ajouter un staff</a>
                   <a href="actions/rank_add.php" role="button" class="btn btn-primary" data-title="Ajouter un rang" data-toggle="lightbox" data-gallery="remoteload"><span class="glyphicon glyphicon-plus"></span> Ajouter un rang</a>
               </div>
               <br>
               <div class="input-group input-group-lg">
               <span class="glyphicon glyphicon-filter input-group-addon" id="basic-addon1"></span>
               <input type="text" id="search" class="form-control" placeholder="Tapez n'importe quoi pour rechercher" aria-describedby="basic-addon1">
               </div>
               <br>
               <ul class="nav nav-tabs">
                	<li role="presentation" <?php echo ($rank_value==0)?"class='active'":"";?>><a href="<?php echo $_SERVER["PHP_SELF"];?>?rank=0">Tous</a></li>
                 <?php while($rank = $queryRanks->fetch(PDO::FETCH_ASSOC)){ ?>
                      <li role="presentation" <?php echo ($rank_value==$rank["rank_id"])?"class='active'":"";?>><a href="<?php echo $_SERVER["PHP_SELF"]."?rank=".$rank["rank_id"];?>"><?php echo $rank["rank_name"];?></a></li>
				   <?php } ?>
               </ul>
               <div class="table-responsive">
                   <table class="table table-striped table-hover">
                       <thead>
                           <tr>
                               <th class="col-sm-4">Identité</th>
                               <th class="col-sm-4">Contact</th>
                               <th class="col-sm-2">Rang</th>
                               <th class="col-sm-2">Actions</th>
                           </tr>
                       </thead>
                       <tbody id="filter-enabled">
                       <?php
                        /** Affichage de tous les membres du staff possédant le rang administratif correspondant **/
                        if($rank_value==0) $queryStaff = $db->query('SELECT * FROM staff JOIN rank ON rank_id_foreign=rank.rank_id ORDER BY nom ASC');
                                else {
                                    $queryStaff = $db->prepare('SELECT * FROM staff JOIN rank ON rank_id_foreign=rank.rank_id WHERE rank_id_foreign=? ORDER BY nom ASC');
                                    $queryStaff->bindParam(1, $rank_value);
                                    $queryStaff->execute();
                                }
                        while($staff = $queryStaff->fetch(PDO::FETCH_ASSOC)){?>
                            <tr>
                                <td class='col-sm-4'><?php echo $staff['prenom']." ".$staff['nom'];?></td>
                                <td class='col-sm-4'><?php echo $staff['mail']."<br>".$staff['tel_fixe']." / ".$staff['tel_port'];?></td>
                                <td class='col-sm-2'><?php echo $staff['rank_name'];?></td>
                            <td class='col-sm-2'>
                            <form method='post' action='staff_liste.php?rank=0'>
                            <div class='btn-group' role='group'>
                                <a type='button' role='button' class='btn btn-default' title="Détails"><span class='glyphicon glyphicon-search'></span></a>
                                <button type='submit' class='btn btn-default'><span class='glyphicon glyphicon-send' title="Contacter par mail"></span></button>
                                <button type='submit' class='btn btn-default'><span class='glyphicon glyphicon-ok' title="Indiquer comme actif"></span></button>
                            </div>
                            <input type='hidden' name='id' value="<?php echo $staff['staff_id'];?>">
                            </form>
                            </td>
                            </tr>
                        <?php } ?>
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
            
        var $rows = $('#filter-enabled tr');
        $('#search').keyup(function(){
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
            $rows.show().filter(function(){
               var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
        });
    </script>
</body>
</html>