<div class="col-sm-2 col-md-2 sidebar" id="large-menu" style="display:block;">
   <ul class="nav nav-sidebar">
      <a id="close" onClick="toggleSideMenu()"><span></span></a>
      <li><a href="dashboard.php" class="main-option"><span class="glyphicon glyphicon-dashboard"></span> Panneau principal</a></li>
      <li><a href="adherents.php" class="main-option"><span class="glyphicon glyphicon-user"></span> Base Client</a></li>
      <li><a href="" class="main-option"><span class="glyphicon glyphicon-credit-card"></span> Forfaits</a></li>
      <li><a href="tarifs_liste.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='tarifs_liste.php') echo "active";?>"><span class="glyphicon glyphicon-scale"></span> Tarifs</a></li>
      <li><a href="planning.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='planning.php') echo "active";?>"><span class="glyphicon glyphicon-time"></span> Planning et Réservations</a></li>
      <li><a href="staff_liste.php?rank=0" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='staff_liste.php') echo "active";?>"><span class="glyphicon glyphicon-briefcase"></span> Gestion du staff</a></li>
      <li><a href="profs_liste.php" class="main-option <?php if(basename($_SERVER['PHP_SELF']) == 'profs_liste.php') echo "active";?>"><span class="glyphicon glyphicon-blackboard"></span> Base Professeurs</a></li>
      <li><a href="" class="main-option"><span class="glyphicon glyphicon-hdd"></span> Ressources</a></li>
      <li><a href="" class="main-option"><span class="glyphicon glyphicon-calendar"></span> Evènements</a></li>
      <li><a href="" class="main-option"><span class="glyphicon glyphicon-thumbs-up"></span> Réseaux Sociaux</a></li>
      <li><a href="stats.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='stats.php') echo "active";?>"><span class="glyphicon glyphicon-stats"></span> Statistiques</a></li>
      <li><a href="../phpmyadmin" class="main-option"><span class="glyphicon glyphicon glyphicon-th-list"></span> Base de données</a></li>
      <li><a href="" class="main-option"><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>
   </ul>
</div>


<div class="col-sm-1 col-md-1 sidebar" id="small-menu" style="display:none;">
    <ul class="nav nav-sidebar">
        <a id="open" onClick="toggleSideMenu()"><span></span></a>
        <li><a href="dashboard.php" class="main-option" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Panneau d'administration"><span class="glyphicon glyphicon-dashboard"></span></a></li>
        <li><a href="adherents.php" class="main-option" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Base client"><span class="glyphicon glyphicon-user"></span></a></li>
        <li><a href="dashboard.php" class="main-option" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Forfaits"><span class="glyphicon glyphicon-credit-card"></span></a></li>
        <li><a href="tarifs_liste.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='tarifs_liste.php') echo "active";?>" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Tarifs"><span class="glyphicon glyphicon-scale"></span></a></li>
        <li><a href="planning.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='planning.php') echo "active";?>" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Planning et Réservations"><span class="glyphicon glyphicon-time"></span></a></li>
        <li><a href="staff_liste.php?rank=0" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='staff_liste.php') echo "active";?>" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Gestion du staff"><span class="glyphicon glyphicon-briefcase"></span></a></li>
        <li><a href="profs_liste.php?rank=0" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='profs_liste.php') echo "active";?>" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Base Professeurs"><span class="glyphicon glyphicon-blackboard"></span></a></li>
        <li><a href="dashboard.php" class="main-option" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Ressources"><span class="glyphicon glyphicon-hdd"></span></a></li>
        <li><a href="dashboard.php" class="main-option" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Evènements"><span class="glyphicon glyphicon-calendar"></span></a></li>
        <li><a href="dashboard.php" class="main-option" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Réseaux Sociaux"><span class="glyphicon glyphicon-thumbs-up"></span></a></li>
        <li><a href="stats.php" class="main-option <?php if(basename($_SERVER['PHP_SELF'])=='stats.php') echo "active";?>" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Statistiques"><span class="glyphicon glyphicon-stats"></span></a></li>
        <li><a href="../phpmyadmin" class="main-option" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Base de données"><span class="glyphicon glyphicon-th-list"></span></a></li>
        <li><a href="dashboard.php" class="main-option" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Déconnexion"><span class="glyphicon glyphicon-off"></span></a></li>
    </ul>
</div>