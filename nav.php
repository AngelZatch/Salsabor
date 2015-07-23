<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryCoursNotif = $db->query("SELECT * FROM cours WHERE paiement_effectue=0");
$coursNotif = $queryCoursNotif->rowCount();


$queryLocationsNotif = $db->query("SELECT * FROM reservations WHERE paiement_effectue=0 AND priorite=1");
$locationsNotif = $queryLocationsNotif->rowCount();
?>
  

  <nav class="navbar navbar-inverse navbar-fixed-top">
   <div class="container-fluid">
       <div class="navbar-header"><a href="" class="navbar-brand">Salsabor</a></div>
       <div id="navbar" class="navbar-collapse collapse">
           <ul class="nav navbar-nav navbar-right">
             <li><a href=""><span class="glyphicon glyphicon-user"></span> Dev_Version</a></li>
              <li class="notification-option">
                      <a href="" class="notification-icon"><span class="glyphicon glyphicon-bell"></span></a>
              </li>
              <li class="notification-option">
                      <a href="" class="notification-icon"><span class="glyphicon glyphicon-folder-open"></span></a>
              </li>
              <li class="notification-option">
                      <a href="" class="notification-icon"><span class="glyphicon glyphicon-calendar"></span><span class="badge"><?php echo $coursNotif;?></span></a>
              </li>
              <li class="notification-option">
                      <a href="" class="notification-icon"><span class="glyphicon glyphicon-scale"></span><span class="badge"><?php echo $locationsNotif;?></span></a>
              </li>
               <li><a href=""><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>
           </ul>
       </div>
   </div>
</nav>