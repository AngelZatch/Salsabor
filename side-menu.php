<div class="sidebar-container">
	<div class="col-sm-2 col-md-2 col-lg-2 sidebar" id="large-menu" style="display:block;">
		<ul class="nav nav-sidebar">

			<li><a class="main-section"><span class="glyphicon glyphicon-star"></span> Actions</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-actions">
				<li class="main-option-container"><a href="inscription" class="main-option"><span class="glyphicon glyphicon-user"></span> Inscription</a></li>
				<li class="main-option-container"><a href="vente" class="main-option"><span class="glyphicon glyphicon-th"></span> Vente</a></li>
				<li class="main-option-container"><a href="reservation" class="main-option"><span class="glyphicon glyphicon-record"></span> Réservation</a></li>
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-transfer"></span> Emploi du temps</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-timetable">
				<li class="main-option-container"><a href="passages" class="main-option"><span class="glyphicon glyphicon-map-marker"></span> Passages</a></li>
				<li class="main-option-container"><a href="planning" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='planning.php') echo "active";?>"><span class="glyphicon glyphicon-time"></span> Planning</a></li>
				<li class="main-option-container"><a href="holidays" class="main-option"><span class="glyphicon glyphicon-leaf"></span> Jours Chômés</a></li>
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-equalizer"></span> Régularisation</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-irregulars">
				<li class="main-option-container"><a href="regularisation/participations" class="main-option"><span class="glyphicon glyphicon-pawn"></span> Participations</a></li>
				<li class="main-option-container"><a href="regularisation/forfaits" class="main-option"><span class="glyphicon glyphicon-queen"></span> Forfaits</a></li>
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-list"></span> Données</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-data">
				<!--<li><a href="adherents.php" class="main-option"><span class="glyphicon glyphicon-user"></span> Base Client</a></li>-->
				<!--<li><a href="staff_liste.php?rank=0" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='staff_liste.php') echo "active";?>"><span class="glyphicon glyphicon-briefcase"></span> Gestion du staff</a></li>-->
				<!--<li><a href="professeurs.php" class="main-option <?php if(basename($_SERVER['PHP_SELF']) == 'professeurs.php') echo "active";?>"><span class="glyphicon glyphicon-blackboard"></span> Base Professeurs</a></li>-->
				<li class="main-option-container"><a href="salles" class="main-option"><span class="glyphicon glyphicon-pushpin"></span> Salles</a></li>
				<li class="main-option-container"><a href="forfaits" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='forfaits.php') echo "active";?>"><span class="glyphicon glyphicon-credit-card"></span> Forfaits</a></li>
				<li class="main-option-container"><a href="tarifs" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='tarifs_liste.php') echo "active";?>"><span class="glyphicon glyphicon-scale"></span> Tarifs de Location</a></li>
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-euro"></span> Finances</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-prices">
				<li class="main-option-container"><a href="echeances" class="main-option"><span class="glyphicon glyphicon-repeat"></span> Echéances</a></li>
				<!--<li><a href="transactions" class="main-option"><span class="glyphicon glyphicon-piggy-bank"></span> Transactions</a></li>-->
			</ul>

			<span></span>

			<li><a class="main-section"><span class="glyphicon glyphicon-tasks"></span> Outils de gestion</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-admin">
				<!--<li><a href="stats.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='stats.php') echo "active";?>"><span class="glyphicon glyphicon-stats"></span> Statistiques</a></li>-->
				<!--<li><a href="about.php" class="main-option"><span class="glyphicon glyphicon-question-sign"></span> A propos</a></li>-->
				<li class="main-option-container"><a href="notifications/settings" class="main-option"><span class="glyphicon glyphicon-cog"></span> Réglages notifs.</a></li>
				<li class="main-option-container"><a href="notifications" class="main-option"><span class="glyphicon glyphicon-bell"></span> Notifications</a></li>
				<li class="main-option-container"><a href="../phpmyadmin" target="_blank" class="main-option"><span class="glyphicon glyphicon-th-list"></span> Base de données</a></li>
				<!--<li><a href="" class="main-option"><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>-->
			</ul>
		</ul>
	</div>
</div>
