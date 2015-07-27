<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d H:i:s');
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));
$queryNextCours = $db->prepare("SELECT * FROM cours JOIN salle ON cours_salle=salle.salle_id WHERE (cours_start>=? AND cours_start<=?) OR ouvert=1 ORDER BY cours_start ASC");
$queryNextCours->bindParam(1, $compare_start);
$queryNextCours->bindParam(2, $compare_end);
$queryNextCours->execute();


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
               <h1 class="page-title"><span class="glyphicon glyphicon-map-marker"></span> Passages</h1>
               <p id="current-time"></p>
               <p id="last-edit"><?php echo ($queryNextCours->rowCount()!=0)?"Il y a ".$queryNextCours->rowCount()." cours à venir":"Aucun cours n'est à venir";?></p>
               <?php while($nextCours = $queryNextCours->fetch(PDO::FETCH_ASSOC)){ ?>
               <div class="panel panel-default">
               	<div class="panel-heading">
               		<div class="panel-title"><?php echo $nextCours["cours_intitule"]." (".$nextCours["salle_name"]." - de ".date_create($nextCours["cours_start"])->format("H:i")." à ".date_create($nextCours["cours_end"])->format("H:i").")";?><button class="btn btn-default">Valider</button></div>
               	</div>
               	<ul class="list-group">
               		<?php								
					  $queryPassages = $db->prepare("SELECT * FROM passages JOIN lecteurs_rfid ON passage_salle=lecteurs_rfid.lecteur_ip WHERE status=0 AND lecteur_lieu=?");
					  $queryPassages->bindParam(1, $nextCours["cours_salle"]);
					  $queryPassages->execute();
					  while($passages = $queryPassages->fetch(PDO::FETCH_ASSOC)){ ?>
						<li class="list-group-item">
							<?php echo $passages["passage_eleve"]." Enregsitré à : ".$passages["passage_date"];?>
							<span class="list-item-option"><span class="glyphicon glyphicon-ok"></span></span>	
						</li>
					<?php } ?>
               	</ul>
			   </div>
			   <?php } ?>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script>
	   var now;
	   function update(){
		   var now = moment().locale('fr').format("DD MMMM YYYY HH:mm:ss");
		   $("#current-time").html(now);
	   }
	   
	   $(document).ready(function(){
		   update();
		   setInterval(update, 1000);
	   });
	</script>
</body>
</html>