<?php
session_start();
require_once 'functions/db_connect.php';
require_once "functions/mails.php";
//require_once "functions/tools.php";
require_once "functions/post_task.php";
require_once "functions/attach_tag.php";
require_once "functions/activate_product.php";
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
					$period_start = "2017-03-01";
					$period_end = "2017-03-30";
					$invoice_seller_id = 78;
					$prestations = $db->query("SELECT pu.prestation_id FROM prestation_users pu
			JOIN prestations p ON pu.prestation_id = p.prestation_id
			WHERE prestation_start > '$period_start' AND prestation_end < '$period_end' AND user_id = $invoice_seller_id AND invoice_id IS NULL")->fetchAll(PDO::FETCH_COLUMN);
					?>
					<pre>
						<?php
print_r($prestations);
echo implode(", ", $prestations);
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
