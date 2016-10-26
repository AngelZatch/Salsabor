<?php
session_start();
require_once 'functions/db_connect.php';
include "functions/mails.php";
include "functions/tools.php";
include "functions/post_task.php";
include "functions/attach_tag.php";
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
					$searchTerms = "An";
					$location = 2;
					/** CODE **/
					$noCards = $db->query("SELECT user_id FROM users u WHERE actif = 1")->fetchAll(PDO::FETCH_COLUMN);


					?>
					<pre>
						<?php
foreach($noCards as $user){
	echo "User actif n°".$user;
	$test = $db->query("SELECT * FROM produits_adherents pa
							JOIN produits p ON pa.id_produit_foreign = p.product_id
							WHERE id_user_foreign = '$user' AND product_name = 'Adhésion Annuelle' AND pa.actif != 2")->rowCount();
	echo " - Nombre d'adhésions annuelles détectées : ".$test;
	if($test == "0"){
		echo " | Création d'une tâche";
		$new_task_id = createTask($db, "Adhésion Annuelle manquante", "Cet utilisateur n'a pas d'adhésion annuelle.", "[USR-".$user."]", null);
		$tag = $db->query("SELECT rank_id FROM tags_user WHERE missing_info_default = 1")->fetch(PDO::FETCH_COLUMN);
		associateTag($db, intval($tag), $new_task_id, "task");
	}
	echo "<br>";
}
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
