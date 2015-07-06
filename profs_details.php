<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET['id'];

// On obtient les détails du professeur
$stmt = $db->prepare('SELECT * FROM professeurs WHERE prof_id=?');
$stmt->bindValue(1, $data);
$stmt->execute();
$details = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare('SELECT * FROM cours JOIN niveau ON cours_niveau=niveau.niveau_id JOIN salle ON cours_salle=salle.salle_id WHERE prof_principal=?');
$stmt->bindValue(1, $data);
$stmt->execute();

// Prix de tous les cours
$totalPrice = 0;
$totalPaid = 0;
$totalDue = 0;
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
               <h1 class="page-title"><span class="glyphicon glyphicon-user"></span>
                   <?php echo $details['prenom']." ".$details['nom'];?>
               </h1>
               <section class="history">
                   <p id="history-title">Historique</p>
                   <table class="table table-striped">
                       <thead>
                           <tr>
                               <th>Intitulé</th>
                               <th>Jour</th>
                               <th>Niveau</th>
                               <th>Lieu</th>
                               <th>Somme</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php while ($history = $stmt->fetch(PDO::FETCH_ASSOC)){?>
                           <tr>
                               <td><?php echo $history['cours_intitule']." ".$history['cours_suffixe'];?></td>
                               <td><?php echo date_create($history['cours_start'])->format('d/m/Y H:i');?> - <?php echo date_create($history['cours_end'])->format('H:i');?></td>
                               <td><?php echo $history['niveau_name'];?></td>
                               <td><?php echo $history['salle_name'];?></td>
                               <td class="<?php echo ($history['paiement_effectue'] != 0)?'payment-done':'payment-due';?>"><?php echo $history['cours_cout_horaire'];?> €</td>
                           </tr>
                           <?php $totalPrice += $history['cours_cout_horaire'];
if($history['paiement_effectue'] != 0)$totalPaid += $history['cours_cout_horaire'];} ?>
                       </tbody>
                   </table>
                    <div class="price-summary">
                       <p>TOTAL</p>
                       <p>Nombre de cours : <?php echo $stmt->rowCount();?></p>
                       <p>Somme totale : <?php echo $totalPrice;?> €</p>
                       <p>Somme déjà réglée : <?php echo $totalPaid;?> €</p>
                       <p>Somme restante : <?php echo $totalDue = $totalPrice - $totalPaid;?> €</p>
                   </div>
               </section><!-- Historique des cours -->
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>