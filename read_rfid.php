<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = explode('*', $_GET["carte"]);
$tag_rfid = $data[0];
$ip_rfid = $data[1];

$date = date_create('now')->format('Y-m-d H:i:s');

$queryLecteur = $db->prepare('SELECT * FROM lecteurs_rfid JOIN salle ON lecteur_lieu=salle.salle_id WHERE lecteur_ip=?');
$queryLecteur->bindParam(1, $ip_rfid);
$queryLecteur->execute();
$lecteur = $queryLecteur->fetch(PDO::FETCH_ASSOC);

$solveAdherents = $db->prepare('SELECT * FROM adherents WHERE numero_rfid=?');
$solveAdherents->bindParam(1, $tag_rfid);
$solveAdherents->execute();
$adherent = $solveAdherents->fetch(PDO::FETCH_ASSOC);

$solveForfaits = $db->prepare('SELECT * FROM produits_adherents JOIN produits ON id_produit=produits.produit_id WHERE id_adherent=?');
$solveForfaits->bindParam(1, $adherent["eleve_id"]);
$solveForfaits->execute();

$getCours = $db->prepare('SELECT * FROM cours WHERE cours_start > ?');
$getCours->bindParam(1, $date);
$getCours->execute();
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
               <h1 class="page-title"><span class="glyphicon glyphicon-qrcode"></span> RFID</h1>
               <?php if($queryLecteur->rowCount() != '0'){?>
               <p>Tag numéro <?php echo $tag_rfid;?></p>
               <p>Ce tag appartient à <?php echo $adherent["eleve_prenom"]." ".$adherent["eleve_nom"];?></p>
               <p>Passage enregistré à : <?php echo $date;?></p>
               <p>Correspond à la salle : <?php echo $lecteur["salle_name"];?></p>
               
               <p>Cet adhérent a les forfaits suivants :</p>
                   <?php while($forfaits = $solveForfaits->fetch(PDO::FETCH_ASSOC)){ ?>
               <p><?php echo $forfaits["produit_nom"];?></p>
                   <?php } ?>
               <p>L'adhérent a validé son passe pour au moins un des cours suivants : </p>
                   <?php while($listeCours = $getCours->fetch(PDO::FETCH_ASSOC)){ ?>
               <p><?php echo $listeCours["cours_intitule"]." (".$listeCours["cours_start"]." - ".$listeCours["cours_end"].")";?></p>
                   <?php } ?>
               <?php } ?>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>