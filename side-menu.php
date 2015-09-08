<div class="col-sm-2 col-md-2 sidebar" id="large-menu" style="display:block;">
	<ul class="nav nav-sidebar">
		<li><a href="dashboard.php" class="main-option"><span class="glyphicon glyphicon-dashboard"></span> Panneau principal</a></li>

		<li><a class="main-section" data-toggle="collapse" href="#collapse-actions" aria-expanded="false"><span class="glyphicon glyphicon-star"></span> Actions</a></li>
		<ul class="nav nav-sidebar collapse" id="collapse-actions">
			<li><a href="inscription.php?status=contact" class="main-option"><span class="glyphicon glyphicon-user"></span> Inscriptions</a></li>
			<li><a href="catalogue.php" class="main-option"><span class="glyphicon glyphicon-th"></span> Vendre un produit</a></li>
			<li><a href="resa_add.php" class="main-option"><span class="glyphicon glyphicon-record"></span> Réservation de salle</a></li>
			<li><a href="eleve_inviter.php" class="main-option"><span class="glyphicon glyphicon-heart-empty"></span> Invitation</a></li>
		</ul>

		<li><a href="#collapse-timetable" class="main-section" data-toggle="collapse"><span class="glyphicon glyphicon-transfer"></span> Emploi du temps</a></li>
		<ul class="nav nav-sidebar collapse" id="collapse-timetable">
			<li><a href="passages.php" class="main-option"><span class="glyphicon glyphicon-map-marker"></span> Passages</a></li>
			<li><a href="irregulars.php" class="main-option"><span class="glyphicon glyphicon-ice-lolly-tasted"></span> Participants irréguliers</a></li>
			<li><a href="planning.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='planning.php') echo "active";?>"><span class="glyphicon glyphicon-time"></span> Planning et Réservations</a></li>
			<li><a href="jours_chomes.php" class="main-option"><span class="glyphicon glyphicon-leaf"></span> Jours Chômés</a></li>
		</ul>

		<li><a href="#collapse-data" class="main-section" data-toggle="collapse"><span class="glyphicon glyphicon-list"></span> Données</a></li>
		<ul class="nav nav-sidebar collapse" id="collapse-data">
			<li><a href="adherents.php" class="main-option"><span class="glyphicon glyphicon-user"></span> Base Client</a></li>
			<li><a href="staff_liste.php?rank=0" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='staff_liste.php') echo "active";?>"><span class="glyphicon glyphicon-briefcase"></span> Gestion du staff</a></li>
			<li><a href="professeurs.php" class="main-option <?php if(basename($_SERVER['PHP_SELF']) == 'professeurs.php') echo "active";?>"><span class="glyphicon glyphicon-blackboard"></span> Base Professeurs</a></li>
			<li><a href="salles.php" class="main-option"><span class="glyphicon glyphicon-pushpin"></span> Salles</a></li>
			<li><a href="forfaits.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='forfaits.php') echo "active";?>"><span class="glyphicon glyphicon-credit-card"></span> Forfaits et abonnements</a></li>
			<li><a href="tarifs_liste.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='tarifs_liste.php') echo "active";?>"><span class="glyphicon glyphicon-scale"></span> Tarifs Location</a></li>
		</ul>

		<li><a href="#collapse-prices" class="main-section" data-toggle="collapse"><span class="glyphicon glyphicon-euro"></span> Finances</a></li>
		<ul class="nav nav-sidebar collapse" id="collapse-prices">
			<li><a href="echeances.php" class="main-option"><span class="glyphicon glyphicon-repeat"></span> Echéances</a></li>
			<li><a href="transactions.php" class="main-option"><span class="glyphicon glyphicon-piggy-bank"></span> Transactions</a></li>
		</ul>

		<li><a href="#collapse-admin" class="main-section" data-toggle="collapse"><span class="glyphicon glyphicon-tasks"></span> Outils de gestion</a></li>
		<ul class="nav nav-sidebar collapse" id="collapse-admin">
			<li><a href="stats.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='stats.php') echo "active";?>"><span class="glyphicon glyphicon-stats"></span> Statistiques</a></li>
			<li><a href="about.php" class="main-option"><span class="glyphicon glyphicon-question-sign"></span> A propos</a></li>
			<li><a href="../phpmyadmin" class="main-option"><span class="glyphicon glyphicon glyphicon-th-list"></span> Base de données</a></li>
			<li><a href="" class="main-option"><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>
		</ul>
	</ul>
</div>
