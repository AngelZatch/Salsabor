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
               <h1 class="page-title"><span class="glyphicon glyphicon-pencil"></span> Page Test !</h1>
      <?php
	$heure_debut = '13:00:00';
	$heure_fin = '15:00:00';
	$lieu = '1';
	$date_debut = '2015-07-01 '.$heure_debut;
	$date_fin = '2015-07-01 '.$heure_fin;
$prof_principal = '3';
$prof_remplacant = '1';

	$findCours = $db->prepare('SELECT * FROM cours WHERE ((cours_start<=? AND cours_end>?) OR (cours_start<? AND cours_end>=?)) AND ((cours_salle=?) OR (prof_principal=? OR prof_remplacant=?))');
	$findCours->bindValue(1, $date_debut);
	$findCours->bindValue(2, $date_debut);
	$findCours->bindValue(3, $date_fin);
	$findCours->bindValue(4, $date_fin);
	$findCours->bindValue(5, $lieu);
	$findCours->bindValue(6, $prof_principal);
	$findCours->bindValue(7, $prof_remplacant);
	$findCours->execute();
	echo $res = $findCours->rowCount();

	$findResa = $db->prepare('SELECT * FROM reservations WHERE reservation_salle=? AND ((reservation_start<=? AND reservation_end>?) OR (reservation_start<? AND reservation_end>=?))');
	$findResa->bindValue(1, $lieu);
	$findResa->bindValue(2, $date_debut);
	$findResa->bindValue(3, $date_debut);
	$findResa->bindValue(4, $date_fin);
	$findResa->bindValue(5, $date_fin);
	$findResa->execute();
	if($findResa->rowCount() != 0){
		$resResa = $findResa->fetch(PDO::FETCH_ASSOC);
		if($resResa['priorite'] != '0'){
			echo "Réservation déjà payée";
		} else {
			echo "Réservation non payée";
		}
	}
	?>

           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script>
	   $('button').click(function(){
		   var id;
		   id = '#'+$(this).attr('id');
		   console.log(id);
			$('#add-options').popoverX({
				target: id,
				placement: 'bottom',
				closeOtherPopovers: true,
			});		
		   $('#add-options').popoverX('toggle');
		   $('#add-options').on('show.bs.modal', function(){
			   $('#add-options').popoverX('refreshPosition');
		   });
	   })
	</script>
</body>
</html>
<script>
</script>