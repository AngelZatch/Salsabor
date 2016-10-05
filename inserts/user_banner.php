<div class="user-banner">
	<div class="user-pp">
		<img src="/Salsabor/<?php echo $details["photo"];?>" alt="<?php echo $details["user_prenom"];?>" class="banner-profile-picture">
	</div>
	<div class="col-sm-8 inline-editable legend">
		<p class="modal-editable-<?php echo $user_id;?>" data-field="user_prenom" data-name="Prénom"><?php echo $details["user_prenom"];?></p>
		<p class="modal-editable-<?php echo $user_id;?>" data-field="user_nom" data-name="Nom"><?php echo $details["user_nom"];?></p>
	</div>
	<span class="col-xs-1 col-sm-1 glyphicon glyphicon-pencil glyphicon-button glyphicon-button-alt glyphicon-button-big" data-toggle="modal" data-target="#edit-modal" data-entry="<?php echo $details["user_id"];?>" data-table="users" title='Modifier les détails de <?php echo $details["user_prenom"]." ".$details["user_nom"];?>'></span>
	<div class="user-summary">
		<div class="col-xs-12 user-labels">
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
		<div class="col-xs-6">
			<div>
				<span class="glyphicon glyphicon-envelope glyphicon-description"></span>
				<p class="modal-editable-<?php echo $user_id;?>" data-field="mail" data-name="Adresse mail"><?php echo $details["mail"];?></p>
			</div>
			<p id="refresh-rfid"></p>
			<?php
			$count = $details["count"];
			if($count > 0){
				if($count > 1){
					$message = $count." tâches non résolues";
				} else {
					$message = $count." tâche non résolue";
				}
				$class = "unsolved";
			} else {
				$message = "Aucune tâche en attente";
				$class = "solved";
			}

			?>
			<a href="user/<?php echo $user_id;?>/taches" id="refresh-tasks" class="<?php echo $class;?>"><span class="glyphicon glyphicon-list-alt"></span> <?php echo $message;?></a>
		</div>
		<div class="col-xs-6">
			<div>
				<span class="glyphicon glyphicon-earphone glyphicon-description"></span>
				<p class="modal-editable-<?php echo $user_id;?>" data-field="telephone" data-name="Téléphone"><?php echo $details["telephone"];?></p>
			</div>
			<p id="refresh-address"></p>
		</div>
	</div>
</div>
