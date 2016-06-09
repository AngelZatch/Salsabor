<div class="sidebar-container">
	<div class="hidden-xs col-md-3 col-lg-2 sidebar separate-scroll" id="large-menu" style="display:block;">
		<ul class="nav nav-sidebar">

			<li><a class="main-section"><span class="glyphicon glyphicon-star"></span> Temps réel</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-actions">
				<li class="main-option-container"><a href="notifications" class="main-option"><span class="glyphicon glyphicon-bell"></span> Notifications <span class="badge sidebar-badge badge-notifications" id="badge-notifications"></span></a></li>
				<li class="main-option-container"><a href="taches/user" class="main-option"><span class="glyphicon glyphicon-list-alt"></span> Tâches à faire <span class="badge sidebar-badge badge-tasks" id="badge-tasks"></span></a></li>
				<li class="main-option-container"><a href="participations" class="main-option"><span class="glyphicon glyphicon-map-marker"></span> Participations</a></li>
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-star"></span> Actions</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-actions">
				<li class="main-option-container"><a href="inscription" class="main-option"><span class="glyphicon glyphicon-user"></span> Inscription</a></li>
				<li class="main-option-container"><a href="vente" class="main-option"><span class="glyphicon glyphicon-th"></span> Vente</a></li>
				<!--<li class="main-option-container"><a href="reservation" class="main-option"><span class="glyphicon glyphicon-record"></span> Réservation</a></li>-->
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-list"></span> Données</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-data">
				<li class="main-option-container"><a href="planning" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='planning.php') echo "active";?>"><span class="glyphicon glyphicon-time"></span> Planning</a></li>
				<li class="main-option-container"><a href="holidays" class="main-option"><span class="glyphicon glyphicon-leaf"></span> Jours Chômés</a></li>
				<li class="main-option-container"><a href="salles" class="main-option"><span class="glyphicon glyphicon-pushpin"></span> Salles</a></li>
				<li class="main-option-container"><a href="forfaits" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='forfaits.php') echo "active";?>"><span class="glyphicon glyphicon-credit-card"></span> Forfaits</a></li>
				<li class="main-option-container"><a href="tarifs" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='tarifs_liste.php') echo "active";?>"><span class="glyphicon glyphicon-scale"></span> Tarifs de Location</a></li>
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-euro"></span> Finances</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-prices">
				<li class="main-option-container"><a href="echeances" class="main-option"><span class="glyphicon glyphicon-repeat"></span> Echéances <span class="badge sidebar-badge" id="badge-echeances"></span></a></li>
				<li class="main-option-container"><a href="rentabilite" class="main-option"><span class="glyphicon glyphicon-usd"></span> Rentabilité <span class="badge sidebar-badge" id="badge-echeances"></span></a></li>
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-equalizer"></span> Régularisation</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-irregulars">
				<li class="main-option-container"><a href="regularisation/participations/all" class="main-option"><span class="glyphicon glyphicon-bishop"></span> Participations <span class="badge sidebar-badge" id="badge-participations"></span></a></li>
				<li class="main-option-container"><a href="regularisation/forfaits" class="main-option"><span class="glyphicon glyphicon-queen"></span> Forfaits</a></li>
			</ul>

			<li><a class="main-section"><span class="glyphicon glyphicon-tasks"></span> Paramétrage</a></li>
			<ul class="nav nav-sub-sidebar" id="collapse-admin">
				<!--<li><a href="stats.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='stats.php') echo "active";?>"><span class="glyphicon glyphicon-stats"></span> Statistiques</a></li>-->
				<li class="main-option-container"><a href="notifications/settings" class="main-option"><span class="glyphicon glyphicon-cog"></span> Notifications</a></li>
				<li class="main-option-container"><a href="tags" class="main-option"><span class="glyphicon glyphicon-tags"></span> &Eacute;tiquettes</a></li>
				<li class="main-option-container"><a href="../phpmyadmin" target="_blank" class="main-option"><span class="glyphicon glyphicon-th-list"></span> Base de données</a></li>
				<li><a href="logout.php" class="main-option"><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>
			</ul>
		</ul>
	</div>
</div>
