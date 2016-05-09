<ul class="nav nav-tabs">
	<li role="presentation" class="active"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
	<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
	<li role="presentation"><a href="user/<?php echo $data;?>/historique">Participations</a></li>
	<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
	<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
	<li role="presentation"><a href="user/<?php echo $data;?>/taches">Tâches</a></li>
	<?php if($details["est_professeur"] == 1){ ?>
	<li role="presentation"><a>Cours donnés</a></li>
	<li role="presentation"><a>Tarifs</a></li>
	<li role="presentation"><a>Statistiques</a></li>
	<?php } ?>
</ul>
