<?php
session_start();
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
					$details = $db->query("SELECT CONCAT(user_prenom, ' ', user_nom) AS user_identity, mail, user_rfid, telephone, CONCAT(rue, ' - ', code_postal, '', ville) AS address FROM users u WHERE user_id = 10599")->fetch(PDO::FETCH_ASSOC);
					if(isset($details["telephone"]) == " "){
						$details["telephone"] = "Ajouter un numéro...";
					}
					?>
					<pre>
						<?php print_r($details); ?>
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
