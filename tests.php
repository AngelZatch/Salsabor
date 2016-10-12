<?php
session_start();
require_once 'functions/db_connect.php';
include "functions/mails.php";
include "functions/tools.php";
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template - Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</legend>
					<?php
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$start = $loading;
					/** CODE **/
					$start_date = new DateTime("2016-07-11 11:00:00");
					$definitive_id = 12;
					?>
					<pre>
						<?php
$string = "user_id=409&user_rfid%5B%5D=Attente+357&user_rfid%5B%5D=Attente+7936&rue%5B%5D=33+av+de+St+Ouen&rue%5B%5D=3+rue+Vincent+Scotto&code_postal%5B%5D=75017&code_postal%5B%5D=5000&ville%5B%5D=Aucune+Valeur&photo=assets%2Fimages%2Flogotype-white.png";

$alt_string = "user_id=5372&user_rfid=Aucune+valeur&photo=assets%2Fimages%2Flogotype-white.png";

parse_str($alt_string, $values);
print_r($values);
foreach($values as $column => $value){
	if(sizeof($values[$column]) > 1 || $value == "Aucune valeur")
		$values[$column] = "NULL";
}
print_r($values);

						?>
					</pre>

					<?php
					/** /CODE **/
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$finish = $loading;
					$total = round(($finish - $start), 4);
					echo "<br>Traitement effectué en ".$total." secondes";
					?>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
		</script>
	</body>
</html>
<script>
</script>
