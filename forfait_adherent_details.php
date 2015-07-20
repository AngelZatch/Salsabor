<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET["id"];

$queryForfait = $db->prepare('SELECT *, produits_adherents.date_activation AS dateActivation FROM produits_adherents JOIN adherents ON id_adherent=adherents.eleve_id JOIN produits ON id_produit=produits.produit_id WHERE id=?');
$queryForfait->bindValue(1, $data);
$queryForfait->execute();
$forfait = $queryForfait->fetch(PDO::FETCH_ASSOC);

$queryCours = $db->prepare('SELECT * FROM cours_participants JOIN cours ON cours_id_foreign=cours.cours_id WHERE produit_adherent_id=?');
$queryCours->bindValue(1, $data);
$queryCours->execute();
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forfait <?php echo $forfait["produit_nom"];?> de <?php echo $forfait["eleve_prenom"]." ".$forfait["eleve_nom"];?> | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-credit-card"></span> Forfait <?php echo $forfait["produit_nom"];?> de <?php echo $forfait["eleve_prenom"]." ".$forfait["eleve_nom"];?></h1>
                <div class="btn-toolbar" id="top-page-buttons">
                   <a href="eleve_details.php?id=<?php echo $forfait["id_adherent"];?>" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour à l'adhérent (<?php echo $forfait["eleve_prenom"]." ".$forfait["eleve_nom"];?>)</a>
                </div> <!-- btn-toolbar -->
              <section>
                   <h2>Détails du forfait adhérent</h2>
                   <pre>
                       <?php print_r($forfait);?>
                   </pre>
               </section>
               <section>
                   <h2>Liste des cours</h2>
                  <?php while($cours = $queryCours->fetch(PDO::FETCH_ASSOC)){ ?>
                   <pre>
                       <?php print_r($cours);?>
                   </pre>
                   <?php } ?>
               </section>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>