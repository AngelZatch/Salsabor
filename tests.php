<?php
session_start();
require_once 'functions/db_connect.php';
include "functions/mails.php";
include "functions/tools.php";
include "functions/post_task.php";
include "functions/attach_tag.php";
include "functions/activate_product.php";
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
					$when = new DateTime();

					?>
					<pre>
						<?php
$age = $db->query("SELECT setting_value FROM settings WHERE setting_code = 'archiv_part'")->fetch(PDO::FETCH_COLUMN);
$delta = "P".$age."M";
$when->sub(new dateinterval($delta));
$when = $when->format("Y-m-d H:i:s");

$db->query("UPDATE participations pr SET archived = 1
				WHERE (pr.status != 2 OR (pr.status = 2 AND produit_adherent_id IS NULL))
				AND passage_date < '$when'");
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
