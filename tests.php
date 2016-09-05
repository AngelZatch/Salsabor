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
					?>
					<pre>
						<?php
$return = getCorrectProductFromTags($db, 2916, 10599);
echo $return;

$value = "02/05/2016";

/*if(preg_match('/\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}/',$value)){
	$value_date = DateTime::createFromFormat("d/m/Y H:i:s", $value);
	$value = $value_date->format("Y-m-d H:i:s");
	echo $value;
}else{
	$value_date = DateTime::createFromFormat("d/m/Y", $value);
	$value = $value_date->format("Y-m-d");
	echo $value;
}*/

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
