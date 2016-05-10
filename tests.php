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
					$title = "l'adresse mail (!MAIL!) de !USER! est incorrecte";
					$pattern = "/(![a-z0-9]+!)/i";
					preg_match_all($pattern, $title, $matches, PREG_SET_ORDER);
					?>
					<pre>
						<?php print_r($matches);?>
					</pre>

					<?php
					//echo $title;
					foreach($matches as $val){
						switch($val[0]){
							case "!MAIL!":
								$mail = "angelzatch@gmail.com";
								$title = preg_replace("/!MAIL!/", $mail, $title);
								break;

							case "!USER!":
								$user = "Andréas Pinbouen";
								$title = preg_replace("/!USER!/", $user, $title);
								break;
						}
					}
					echo $title;
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
