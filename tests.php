<?php
require_once "functions/db_connect.php";
    $db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$heures = $db->query('SELECT plages_resa_debut, plages_resa_fin FROM plages_reservations WHERE plages_resa_jour=1')->fetchAll(PDO::FETCH_ASSOC);
?>
<pre>
    <?php
    print_r($heures);
for($i = 1; $i <= 3; $i++){
    echo $heure_debut_resa = $heures[$i-1]['plages_resa_debut'];
    echo $heure_fin_resa = $heures[$i-1]['plages_resa_fin'];}
    ?>
</pre>