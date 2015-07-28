<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$coursNotif = $db->query("SELECT * FROM cours WHERE paiement_effectue=0")->rowCount();

$locationsNotif = $db->query("SELECT * FROM reservations WHERE paiement_effectue=0 AND priorite=1")->rowCount();

$nombrePassages = $db->query("SELECT * FROM passages WHERE status=0")->rowCount();

$queryPassages = $db->query("SELECT * FROM passages JOIN adherents ON passage_eleve=adherents.numero_rfid");
?>
  

  <nav class="navbar navbar-inverse navbar-fixed-top">
   <div class="container-fluid">
       <div class="navbar-header"><a href="dashboard.php" class="navbar-brand"><img src="assets/images/logotest.png" alt="Salsabor Gestion" style="height:100%;"></a></div>
       <div id="navbar" class="navbar-collapse collapse">
           <ul class="nav navbar-nav navbar-right">
             <li><a href=""><span class="glyphicon glyphicon-user"></span> Dev_Version</a></li>
              <li class="notification-option">
                      <a href="passages.php" class="notification-icon"><span class="glyphicon glyphicon-map-marker"></span><span class="badge"><?php echo $nombrePassages;?></span></a>
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