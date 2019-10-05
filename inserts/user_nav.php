<ul class="nav nav-tabs">
	<li role="presentation" class="active"><a href="user/<?php echo $user_id;?>">Informations personnelles</a></li>
	<li role="presentation"><a href="user_subscriptions.php?id=<?php echo $user_id;?>">Abonnements</a></li>
	<li role="presentation"><a href="user_history.php?id=<?php echo $user_id;?>">Participations</a></li>
	<li role="presentation"><a href="user/<?php echo $user_id;?>/achats">Achats</a></li>
	<li role="presentation"><a href="user_reservations.php?id=<?php echo $user_id;?>">Réservations</a></li>
	<li role="presentation"><a href="user_tasks.php?id=<?php echo $user_id;?>">Tâches</a></li>
	<?php if($is_teacher == 1){ ?>
	<li role="presentation"><a>Cours donnés</a></li>
	<li role="presentation"><a>Tarifs</a></li>
	<li role="presentation"><a>Statistiques</a></li>
	<?php } ?>
</ul>
