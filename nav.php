<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$locationsNotif = $db->query("SELECT * FROM reservations WHERE paiement_effectue=0 AND priorite=1")->rowCount();

$queryPassages = $db->query("SELECT * FROM passages JOIN adherents ON passage_eleve=adherents.numero_rfid");
?>
  

  <nav class="navbar navbar-inverse navbar-fixed-top">
   <div class="container-fluid">
       <div class="navbar-header"><a href="dashboard.php" class="navbar-brand"><img src="assets/images/logotest.png" alt="Salsabor Gestion" style="height:100%;"></a></div>
       <div id="navbar" class="navbar-collapse collapse">
           <ul class="nav navbar-nav navbar-right">
             <li><a href=""><span class="glyphicon glyphicon-user"></span> Dev_Version</a></li>
              <li class="notification-option" title="Passages en attente de traitement"><a href="passages.php" class="notification-icon"><span class="glyphicon glyphicon-map-marker"></span><span class="badge" id="badge-passages"></span></a>
              </li>
              <li class="notification-option" title="Participants à un cours sans forfait"><a href="passages.php" class="notification-icon"><span class="glyphicon glyphicon-ice-lolly-tasted"></span><span class="badge" id="badge-participants"></span></a>
              </li>
              <li class="notification-option" title="Echéances en retard"><a href="echeances.php" class="notification-icon"><span class="glyphicon glyphicon-repeat"></span><span class="badge" id="badge-echeances"></span></a>
              </li>
               <li><a href=""><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>
           </ul>
       </div>
   </div>
</nav>