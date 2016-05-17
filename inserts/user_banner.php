<div class="user-banner">
	<div class="user-pp">
		<img src="<?php echo $details["photo"];?>" alt="<?php echo $details["user_prenom"];?>" class="profile-picture">
	</div>
	<p class="legend"><?php echo $details["user_prenom"]." ".$details["user_nom"];?></p>
	<div class="user-summary">
		<div class="col-lg-12 user-labels">
			<?php if($details["actif"] == 1){ ?>
			<span class="label label-success">Actif</span>
			<?php } else {
	if(isset($details["date_last"]) && $details["date_last"] != "0000-00-00 00:00:00"){ ?>
			<span class="label label-danger">Inactif depuis le <?php echo date_create($details["date_last"])->format("d/m/Y");?></span>
			<?php } else { ?>
			<span class="label label-danger">Inactif</span>
			<?php }
} ?>
		</div>
		<div class="col-lg-6">
			<p id="refresh-mail"></p>
			<p id="refresh-rfid"></p>
			<p id="refresh-tasks"><span class="glyphicon glyphicon-list-alt"></span> <?php if($details["count"] > 0){echo $details["count"]." tâche(s) non résolue(s)";} else { echo "Aucune tâche en attente";}?></p>
		</div>
		<div class="col-lg-6">
			<p id="refresh-phone"></p>
			<p id="refresh-address"></p>
		</div>
	</div>
</div>
