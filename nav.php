<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryCours = $db->query("SELECT * FROM cours WHERE paiement_effectue=0");
$cours = $queryCours->rowCount();

$queryLocations = $db->query("SELECT * FROM reservations WHERE paiement_effectue=0");
$locations = $queryLocations->rowCount();
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
                      <a href="" class="notification-icon"><span class="glyphicon glyphicon-calendar"></span><span class="badge"><?php echo $cours;?></span></a>
              </li>
              <li class="notification-option">
                      <a href="" class="notification-icon"><span class="glyphicon glyphicon-scale"></span><span class="badge"><?php echo $locations;?></span></a>
              </li>
               <li><a href=""><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>
           </ul>
       </div>
   </div>
</nav>