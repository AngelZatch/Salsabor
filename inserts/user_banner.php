<div class="user-banner">
	<div class="user-pp">
		<img src="<?php echo $details["photo"];?>" alt="" class="profile-picture">
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
			<p><span class="glyphicon glyphicon-envelope"></span> <?php echo $details["mail"];?></p>
			<p><span class="glyphicon glyphicon-barcode"></span> <?php echo $details["user_rfid"];?></p>
			<p><span class="glyphicon glyphicon-list-alt"></span> <?php if($details["count"] > 0){echo $details["count"]." tâche non résolue";} else { echo "Aucune tâche en attente";}?></p>
		</div>
		<div class="col-lg-6">
			<p><span class="glyphicon glyphicon-earphone"></span> <?php echo $details["telephone"];?></p>
			<p><span class="glyphicon glyphicon-home"></span> <?php echo $details["rue"];?> - <?php echo $details["code_postal"]." ".$details["ville"];?></p>
		</div>
	</div>
</div>
