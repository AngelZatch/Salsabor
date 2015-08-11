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
              <p id="current-time"></p>
               <h1 class="page-title"><span class="glyphicon glyphicon-map-marker"></span> Passages</h1>
               <p id="last-edit"><?php echo ($queryNextCours->rowCount()!=0)?"".$queryNextCours->rowCount()." cours sont actuellement ouvert(s) aux enregistrements":"Aucun cours n'est à venir";?></p>
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
               			<p id="cours-people">Actuellement <span class="cours-count"></span> participants, dont <span class="cours-count-checked">0</span> validés.</p>
					</div>
               	</div>
               	<?php
			  $queryPassages = $db->prepare("SELECT * FROM passages JOIN lecteurs_rfid ON passage_salle=lecteurs_rfid.lecteur_ip JOIN adherents ON passage_eleve=adherents.numero_rfid WHERE ((status=0 OR status=3) AND lecteur_lieu=? AND passage_date>=? AND passage_date<=?) OR (status=2 AND cours_id=?)");
			  $queryPassages->bindParam(1, $nextCours["cours_salle"]);
			  $queryPassages->bindParam(2, date("Y-m-d H:i:s", strtotime($nextCours["cours_start"].'-60MINUTES')));
			  $queryPassages->bindParam(3, date("Y-m-d H:i:s", strtotime($nextCours["cours_start"].'+20MINUTES')));
			  $queryPassages->bindParam(4, $nextCours["cours_id"]);
			  $queryPassages->execute();
			  ?>
               	<ul class="list-group">
					<div class="container-fluid row droppable">
					  <?php								
						  while($passages = $queryPassages->fetch(PDO::FETCH_ASSOC)){
							  switch($passages["status"]){
								  case 0:
									  $status = "warning";
									  break;
									  
								  case 2:
									  $status = "success";
									  break;
									  
								  case 3:
									  $status = "danger";
									  break;
							  };
							  $queryEcheances = $db->query("SELECT * FROM produits_echeances JOIN produits_adherents ON id_produit_adherent=produits_adherents.id_transaction WHERE echeance_effectuee=2 AND id_adherent=$passages[eleve_id]")->rowCount();
						?>
							<li class="list-group-item list-group-item-<?php echo $status;?> draggable col-sm-12">
								<p class="col-sm-3 eleve-infos">
									<?php echo $passages["eleve_prenom"]." ".$passages["eleve_nom"];?>
									<input type="hidden" class="eleve-id" value="<?php echo $passages["eleve_id"];?>">
									<input type="hidden" class="passage-id" value="<?php echo $passages["passage_id"];?>">
								</p>
								<p class="col-sm-1 eleve-tag"><?php echo $passages["passage_eleve"];?></p>
								<p class="col-sm-3">Enregsitré à <?php echo date_create($passages["passage_date"])->format("H:i:s");?></p>
								<?php if($queryEcheances != 0){ ?>
								<p class="col-sm-3"><span class="glyphicon glyphicon-repeat glypicon-danger"></span></p>
								<div class="col-sm-2 record-options">
								<?php } else { ?>
								<div class="col-sm-5 record-options">
								<?php } ?>
								<?php if ($passages["status"] == 0 || $passages["status"] == 3){?>
									<span class="list-item-option validate-record glyphicon glyphicon-ok" title="Valider l'enregistrement comme étant bien pour ce cours"></span>
								<?php } else if($passages["status"] == 2) {  ?>
									<span class="list list-item-option unvalidate-record glyphicon glyphicon-remove" title="Annuler la validation de cet enregistrement"></span>
								<?php } ?>
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
	   $(".close-cours").click(function(){
		   var cours = $(this).closest(".panel").find("#cours-id").val();
		   if($(this).closest(".panel").find(".cours-count").html() != $(this).closest(".panel").find(".cours-count-checked").html()){
			   $.notify("Impossible de fermer un cours dont les passages n'ont pas tous été validés.", {globalPosition: "right bottom", className:"error"});
		   } else {
			   $.post("functions/close_cours.php", {cours}).done(function(data){
				   $.notify("Cours fermé.", {globalPosition:"right bottom", className:"success"});
			   });   
			   $(this).closest(".panel").hide('200');
		   }
	   });
	   
	   $(".validate-all").click(function(){
		   var cours = $(this).children("input").val();
		   var clicked = $(this);

	   });
	   
	   $(".validate-record").click(function(){
		   var clicked = $(this);
		   var cours_id = clicked.closest(".panel").find("#cours-id").val();
		   var eleve_id = clicked.parents().siblings(".eleve-infos").children("input.eleve-id").val();
		   var passage_id = clicked.parents().siblings(".eleve-infos").children("input.passage-id").val();
		   var rfid = clicked.parents().siblings(".eleve-tag").html();
		   console.log(eleve_id);
		   $.post("functions/validate_record.php", {cours_id, eleve_id, passage_id, rfid}).done(function(data){
			   clicked.closest("li").removeClass('list-group-item-warning');
			   clicked.closest("li").addClass("list-group-item-success");
			   showSuccessNotif(data);
		   });
	   });
	   
	   $(".unvalidate-record").click(function(){
		   var clicked = $(this);
		   var cours_id = clicked.closest(".panel").find("#cours-id").val();
		   var eleve_id = clicked.parents().siblings(".eleve-infos").children("input.eleve-id").val();
		   var passage_id = clicked.parents().siblings(".eleve-infos").children("input.passage-id").val();
		   var rfid = clicked.parents().siblings(".eleve-tag").html();
		   $.post("functions/unvalidate_record.php", {cours_id, eleve_id, passage_id, rfid}).done(function(data){
			   clicked.closest("li").removeClass('list-group-item-success');
			   clicked.closest("li").addClass("list-group-item-warning");
			   $.notify("Passage supprimé.", {globalPosition:"right bottom", className:"success"});
		   });
	   })
	</script>
</body>
</html>