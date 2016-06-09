<div class="user-banner">
	<div class="user-pp">
		<img src="<?php echo $details["photo"];?>" alt="<?php echo $details["user_prenom"];?>" class="profile-picture">
	</div>
	<div class="col-sm-9 inline-editable legend">
		<p class="editable inline-first" id="user_prenom" data-input="text" data-table="users" data-column="user_prenom" data-target="<?php echo $data;?>" data-value="value"><?php echo $details["user_prenom"];?></p>
		<p class="editable" id="user_nom" data-input="text" data-table="users" data-column="user_nom" data-target="<?php echo $data;?>" data-value="value"><?php echo $details["user_nom"];?></p>
	</div>
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
			<div>
				<span class="glyphicon glyphicon-envelope glyphicon-description"></span>
				<p class="editable" id="refresh-mail" data-input="mail" data-table="users" data-column="mail" data-target="<?php echo $data;?>" data-value="<?php echo ($details["mail"]="")?"no-value":"value";?>"><?php echo $details["mail"];?></p>
			</div>
			<p id="refresh-rfid"></p>
			<p id="refresh-tasks"><span class="glyphicon glyphicon-list-alt"></span> <?php if($details["count"] > 0){echo $details["count"]." tâche(s) non résolue(s)";} else { echo "Aucune tâche en attente";}?></p>
		</div>
		<div class="col-lg-6">
			<div>
				<span class="glyphicon glyphicon-earphone glyphicon-description"></span>
				<p class="editable" id="refresh-phone" data-input="tel" data-table="users" data-column="telephone" data-target="<?php echo $data;?>" data-value="<?php echo ($details["mail"]="")?"no-value":"value";?>"><?php echo $details["telephone"];?></p>
			</div>
			<p id="refresh-address"></p>
		</div>
	</div>
</div>
