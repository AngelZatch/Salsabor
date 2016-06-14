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
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</legend>
					<?php
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$start = $loading;
					/** CODE **/
					$hook = 2094;
					$new_start = new DateTime('2016-06-14 13:45:00');
					$new_end = new DateTime('2016-06-14 14:45:00');

					$hook_times = $db->query("SELECT cours_start, cours_end FROM cours WHERE cours_id = $hook")->fetch(PDO::FETCH_ASSOC);
					$old_start = new DateTime($hook_times["cours_start"]);
					$old_end = new DateTime($hook_times["cours_end"]);

					$start_delta = $old_start->diff($new_start);
					$end_delta = $old_end->diff($new_end);
					?>
					<pre>
						<?php
print_r($hook_times);
print_r($start_delta);
echo $start_delta->format("%R%h hours, %i minutes");
if($new_start < $old_start){
	$old_start->sub(new DateInterval("P".$start_delta->format("%d")."DT".$start_delta->format("%h")."H".$start_delta->format("%i")."M"));
} else {
	$old_start->add(new DateInterval("P".$start_delta->format("%d")."DT".$start_delta->format("%h")."H".$start_delta->format("%i")."M"));
}
$old_end->add(new DateInterval("PT".$start_delta->format("%h")."H".$start_delta->format("%i")."M"));
echo "<br>";
echo $old_start->format("Y-m-d H:i:s");
echo "<br>";
echo $old_end->format("Y-m-d H:i:s");

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
