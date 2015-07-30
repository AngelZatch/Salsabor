<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$compare_start = date_create('now')->format('Y-m-d H:i:s');
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));
$queryNextCours = $db->prepare("SELECT * FROM cours JOIN salle ON cours_salle=salle.salle_id WHERE (cours_start>=? AND cours_start<=? AND ouvert=1) OR ouvert=1 ORDER BY cours_start ASC");
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
               <p id="last-edit"><?php echo ($queryNextCours->rowCount()!=0)?"".$queryNextCours->rowCount()." cours sont actuellement ouvert aux enregistrements":"Aucun cours n'est à venir";?></p>
               <?php while($nextCours = $queryNextCours->fetch(PDO::FETCH_ASSOC)){ ?>
               <div class="panel panel-default">
               	<div class="panel-heading">
               		<div class="panel-title">
               			<div class="cours-infos">
               				<span class="cours-title"><?php echo $nextCours["cours_intitule"];?></span>
               				<span class="cours-hours">
               					<span class="relative-start"><?php echo $nextCours["cours_start"];?></span>, de
               					<?php echo date_create($nextCours["cours_start"])->format("H:i")." à ".date_create($nextCours["cours_end"])->format("H:i");?>
							</span>
               			</div>
						<span class="list-item-option close-cours glyphicon glyphicon-ok-sign" title="Fermer le cours">
              				<input type="hidden" id="cours-id" value="<?php echo $nextCours["cours_id"];?>">
						</span>
               			<span class="list-item-option validate-all glyphicon glyphicon-floppy-saved" title="Valider tous les enregistrements"></span>
               			<p id="cours-people">Actuellement <span class="cours-count">0</span> participants, dont <span class="cours-count-checked">0</span> validés.</p>
					</div>
               	</div>
               	<?php
			  $queryPassages = $db->prepare("SELECT * FROM passages JOIN lecteurs_rfid ON passage_salle=lecteurs_rfid.lecteur_ip JOIN adherents ON passage_eleve=adherents.numero_rfid WHERE status!=1 AND lecteur_lieu=? AND passage_date>=? AND passage_date<=?");
			  $queryPassages->bindParam(1, $nextCours["cours_salle"]);
			  $queryPassages->bindParam(2, date("Y-m-d H:i:s", strtotime($nextCours["cours_start"].'-60MINUTES')));
			  $queryPassages->bindParam(3, date("Y-m-d H:i:s", strtotime($nextCours["cours_start"].'+20MINUTES')));
			  $queryPassages->execute();
			  ?>
               	<ul class="list-group">
					<div class="container-fluid row">
					  <?php								
						  while($passages = $queryPassages->fetch(PDO::FETCH_ASSOC)){
							$status = ($passages["status"] == 0)?"warning":"success";
						?>
							<li class="list-group-item list-group-item-<?php echo $status;?> draggable col-sm-12">
								<p class="col-sm-3 eleve-infos">
									<?php echo $passages["eleve_prenom"]." ".$passages["eleve_nom"];?>
									<input type="hidden" class="eleve-id" value="<?php echo $passages["eleve_id"];?>">
								</p>
								<p class="col-sm-3 eleve-tag"><?php echo $passages["passage_eleve"];?></p>
								<p class="col-sm-3">Enregsitré à <?php echo date_create($passages["passage_date"])->format("H:i:s");?></p>
								<div class="col-sm-3 <?php echo $status = ($passages["status"] == 0)?"record-waiting":"record-done";?>">
									<span class="list-item-option validate-record glyphicon glyphicon-ok" title="Valider l'enregistrement comme étant bien pour ce cours"></span>
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
	   
	   $(".close-cours").click(function(){
		   var cours = $(this).children("input").val();
		   var clicked = $(this);
		   $.post("functions/validate_all_records.php", {cours}).done(function(data){
			   clicked.parents("panel-default").hide();
			   $.notify("Cours fermé.", {globalPosition:"right bottom", className:"success"});
		   });
	   });
	   
	   $(".validate-record").click(function(){
		   var clicked = $(this);
		   var cours_id = clicked.closest(".panel").find("#cours-id").val();
		   var eleve_id = clicked.parents().siblings(".eleve-infos").children("input").val();
		   var rfid = clicked.parents().siblings(".eleve-tag").html();
/*		   $.post("functions/validate_record.php", {cours_id, eleve_id, rfid}).done(function(data){
			   console.log(data);
			   clicked.closest("li").removeClass('list-group-item-warning');
			   clicked.closest("li").addClass("list-group-item-success");
			   $.notify("Passage validé.", {globalPosition:"right bottom", className:"success"});
		   });*/
	   });
       
       $(".relative-start").each(function(){
           $(this).html(moment($(this).html(), "YYYY-MM-DD HH:ii:ss", 'fr').fromNow());
       });
	   
	   $(".list-group-item").click(function(){
		   var eleve_id = $(this).children(".eleve-tag").html();
		   var cours_id = $(this).closest(".panel").find("#cours-id").val();
		   //console.log(cours_id);
	   })
	</script>
</body>
</html>