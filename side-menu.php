<div class="col-sm-2 col-md-2 sidebar" id="large-menu" style="display:block;">
   <ul class="nav nav-sidebar">
      <a id="close" onClick="toggleSideMenu()"><span></span></a>
      <li><a href="dashboard.php" class="main-option"><span class="glyphicon glyphicon-dashboard"></span> Panneau principal</a></li>
      
      <li><a class="main-section" data-toggle="collapse" href="#collapse-actions" aria-expanded="false"><span class="glyphicon glyphicon-star"></span> Actions</a></li>
      <ul class="nav nav-sidebar collapse" id="collapse-actions">
      	<li><a href="eleve_add.php" class="main-option"><span class="glyphicon glyphicon-user"></span> Inscriptions</a></li>
      	<li><a href="vente_forfait.php" class="main-option"><span class="glyphicon glyphicon-credit-card"></span> Vente d'abonnement</a></li>
      	<li><a href="resa_add.php" class="main-option"><span class="glyphicon glyphicon-record"></span> Réservation de salle</a></li>
      	<li><a href="eleve_inviter.php" class="main-option"><span class="glyphicon glyphicon-heart-empty"></span> Invitation</a></li>
      </ul>
      
      <li><a href="#collapse-timetable" class="main-section" data-toggle="collapse"><span class="glyphicon glyphicon-transfer"></span> Emploi du temps</a></li>
      <ul class="nav nav-sidebar collapse" id="collapse-timetable">
      	<li><a href="passages.php" class="main-option"><span class="glyphicon glyphicon-map-marker"></span> Passages</a></li>
      	<li><a href="planning.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='planning.php') echo "active";?>"><span class="glyphicon glyphicon-time"></span> Planning et Réservations</a></li>
      	<li><a href="echeances.php" class="main-option"><span class="glyphicon glyphicon-repeat"></span> Echéances</a></li>
      </ul>
      
      <li><a href="#collapse-data" class="main-section" data-toggle="collapse"><span class="glyphicon glyphicon-list"></span> Données</a></li>
      <ul class="nav nav-sidebar collapse" id="collapse-data">
      	<li><a href="adherents.php" class="main-option"><span class="glyphicon glyphicon-user"></span> Base Client</a></li>
      	<li><a href="staff_liste.php?rank=0" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='staff_liste.php') echo "active";?>"><span class="glyphicon glyphicon-briefcase"></span> Gestion du staff</a></li>
      	<li><a href="profs_liste.php" class="main-option <?php if(basename($_SERVER['PHP_SELF']) == 'profs_liste.php') echo "active";?>"><span class="glyphicon glyphicon-blackboard"></span> Base Professeurs</a></li>
      	<li><a href="salles.php" class="main-option"><span class="glyphicon glyphicon-pushpin"></span> Salles</a></li>
      </ul>
      
      <li><a href="#collapse-prices" class="main-section" data-toggle="collapse"><span class="glyphicon glyphicon-euro"></span> Tarifs</a></li>
      <ul class="nav nav-sidebar collapse" id="collapse-prices">
      	<li><a href="forfaits.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='forfaits.php') echo "active";?>"><span class="glyphicon glyphicon-credit-card"></span> Forfaits et abonnements</a></li>
      	<li><a href="tarifs_liste.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='tarifs_liste.php') echo "active";?>"><span class="glyphicon glyphicon-scale"></span> Tarifs Location</a></li>
      </ul>
      
      <li><a href="#collapse-admin" class="main-section" data-toggle="collapse"><span class="glyphicon glyphicon-tasks"></span> Outils de gestion</a></li>
      <ul class="nav nav-sidebar collapse" id="collapse-admin">
      	<li><a href="stats.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='stats.php') echo "active";?>"><span class="glyphicon glyphicon-stats"></span> Statistiques</a></li>
      	<li><a href="../phpmyadmin" class="main-option"><span class="glyphicon glyphicon glyphicon-th-list"></span> Base de données</a></li>
      	<li><a href="" class="main-option"><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>
      </ul>
   </ul>
</div>


<div class="col-sm-1 col-md-1 sidebar" id="small-menu" style="display:none;">
    <ul class="nav nav-sidebar">
        <a id="open" onClick="toggleSideMenu()"><span></span></a>
        <li><a href="dashboard.php" class="main-option" data-toggle="tooltip" data-placement="right" title="Panneau d'administration"><span class="glyphicon glyphicon-dashboard"></span></a></li>
        
        <li><a href="passages.php" class="main-option" data-toggle="tooltip" data-placement="right" title="Passages"><span class="glyphicon glyphicon-map-marker"></span></a></li>
        <li><a href="planning.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='planning.php') echo "active";?>" data-toggle="tooltip"  data-placement="right" title="Planning et Réservations"><span class="glyphicon glyphicon-time"></span></a></li>
        
        <li><a href="adherents.php" class="main-option" data-toggle="tooltip" data-placement="right" title="Base client"><span class="glyphicon glyphicon-user"></span></a></li>
        <li><a href="staff_liste.php?rank=0" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='staff_liste.php') echo "active";?>" data-toggle="tooltip"  data-placement="right" title="Gestion du staff"><span class="glyphicon glyphicon-briefcase"></span></a></li>
        <li><a href="profs_liste.php?rank=0" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='profs_liste.php') echo "active";?>" data-toggle="tooltip"  data-placement="right" title="Base Professeurs"><span class="glyphicon glyphicon-blackboard"></span></a></li>
        
        <li><a href="forfaits.php" class="main-option" data-toggle="tooltip" data-placement="right" title="Forfaits et abonnements"><span class="glyphicon glyphicon-credit-card"></span></a></li>
        <li><a href="tarifs_liste.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='tarifs_liste.php') echo "active";?>" data-toggle="tooltip"  data-placement="right" title="Tarifs Location"><span class="glyphicon glyphicon-scale"></span></a></li>
        
        <li><a href="stats.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='stats.php') echo "active";?>" data-toggle="tooltip"  data-placement="right" title="Statistiques"><span class="glyphicon glyphicon-stats"></span></a></li>
        <li><a href="../phpmyadmin" class="main-option" data-toggle="tooltip" data-placement="right" title="Base de données"><span class="glyphicon glyphicon-th-list"></span></a></li>
        <li><a href="dashboard.php" class="main-option" data-toggle="tooltip" data-placement="right" title="Déconnexion"><span class="glyphicon glyphicon-off"></span></a></li>
    </ul>
</div>