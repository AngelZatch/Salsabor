<?php
setlocale(LC_TIME, 'fr_FR.utf8');

//echo date('l', strtotime('Sunday + 1 DAYS'));

//echo strftime('%A', mktime(0,0,0,(date_create('2015-08-06')->format('j')),(date_create('2015-08-06')->format('n')),(date_create('2015-08-06')->format('Y'))));

echo $nombre_repetitions = (strtotime('2015-08-27') - strtotime('2015-08-06'))/(86400*1)+1;
?>