<?php
require_once 'functions/db_connect.php';
include "functions/mails.php";
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
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</legend>
					<?php
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$start = $loading;
					/** CODE **/
					/*foreach(glob("assets/pictures/*.jpg") as $filename){
						preg_match("/[0-9-]+/", $filename, $matches);
						$user_id = $matches[0];

						$photo = $db->query("UPDATE users SET photo = 'assets/pictures/$user_id.jpg' WHERE user_id = '$user_id'");
					}
					Took 21 seconds for 338 items*/

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
