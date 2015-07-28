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
               <div class="alert alert-custom alert-success" id="passage-added" style="display:none;">Passage enregistré.</div>
               <p id="current-time"></p>
               <p id="last-edit"><?php echo ($queryNextCours->rowCount()!=0)?"Il y a ".$queryNextCours->rowCount()." cours à venir":"Aucun cours n'est à venir";?></p>
               <?php while($nextCours = $queryNextCours->fetch(PDO::FETCH_ASSOC)){ ?>
               <div class="panel panel-default">
               	<div class="panel-heading">
               		<div class="panel-title">
               			<?php echo $nextCours["cours_intitule"]." (".$nextCours["salle_name"]." - de ".date_create($nextCours["cours_start"])->format("H:i")." à ".date_create($nextCours["cours_end"])->format("H:i").")";?>
               			<input type="hidden" name="cours_id" value="<?php echo $nextCours["cours_id"];?>">
               			<span class="list-item-option"><span class="glyphicon glyphicon-floppy-saved" title="Valider tous les enregistrements"></span></span>
					</div>
               	</div>
               	<ul class="list-group">
					<div class="container-fluid row">
					  <?php								
						  $queryPassages = $db->prepare("SELECT * FROM passages JOIN lecteurs_rfid ON passage_salle=lecteurs_rfid.lecteur_ip JOIN adherents ON passage_eleve=adherents.numero_rfid WHERE status!=1 AND lecteur_lieu=?");
						  $queryPassages->bindParam(1, $nextCours["cours_salle"]);
						  $queryPassages->execute();
						  while($passages = $queryPassages->fetch(PDO::FETCH_ASSOC)){
							$status = ($passages["status"] == 0)?"warning":"success";
						?>
							<li class="list-group-item list-group-item-<?php echo $status;?> draggable col-sm-12">
								<p class="col-sm-4"><?php echo $passages["eleve_prenom"]." ".$passages["eleve_nom"]." (".$passages["passage_eleve"].")";?></p>
								<p class="col-sm-4">Enregsitré à <?php echo date_create($passages["passage_date"])->format("H:i:s");?></p>
								<div class="col-sm-4">
									<span class="list-item-option glyphicon glyphicon-ok" title="Valider l'enregistrement comme étant bien pour ce cours"><input type="hidden" id="eleve_id" value="<?php echo $passages["eleve_id"]."*".$nextCours["cours_id"]."*".$passages["passage_eleve"];?>"></span>
								</div>
							</li>
						<?php } ?></div>
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
	   
	   $(".list-item-option").click(function(){
		   var token = $(this).children("input").val().split("*");
		   var eleve_id = token[0];
		   var cours_id = token[1];
		   var rfid = token[2];
		   var clicked = $(this);
		   $.post("functions/validate_record.php", {cours_id, eleve_id, rfid}).done(function(data){
			   console.log(data);
			   clicked.closest("li").removeClass('list-group-item-warning');
			   clicked.closest("li").addClass("list-group-item-success");
			   $('#passage-added').show().delay('4000').hide('600');
		   });
	   })
	</script>
</body>
</html>