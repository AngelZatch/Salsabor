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
					//Have to copy all the participations to the new table
					set_time_limit(0);
					$duplicates = $db->query("SELECT *, count(*) AS duplicates FROM participations GROUP BY cours_id, user_id HAVING duplicates > 1");
					while($duplicate = $duplicates->fetch(PDO::FETCH_ASSOC)){
						echo "Passage ".$duplicate["passage_id"]." | user ".$duplicate["user_id"]." | cours ".$duplicate["cours_id"]." | produit ".$duplicate["produit_adherent_id"]."<br>";
						$delete = $db->query("DELETE FROM participations WHERE passage_id = '$duplicate[passage_id]'");
					}
					/*$insertedLines = 0;
					$participations = $db->query("SELECT * FROM old_cours_participants cp
													LEFT JOIN cours c ON cp.cours_id_foreign = c.cours_id
													LEFT JOIN lecteurs_rfid lr ON c.cours_salle = lr.lecteur_ip");
					while($participation = $participations->fetch(PDO::FETCH_ASSOC)){
						$current_id = $participation["id"];
						$cours_id = $participation["cours_id_foreign"];
						$user_id = $participation["eleve_id_foreign"];
						$produit_adherent_id = $participation["produit_adherent_id"];
						if($participation["cours_start"] != null){
							$date_passage = $participation["cours_start"];
						}
						if($participation["lecteur_ip"] != null){
							$room_token = $participation["lecteur_ip"];
						}
						$existant = $db->query("SELECT * FROM participations WHERE user_id = '$user_id' AND cours_id = '$cours_id'")->rowCount();
						if($existant == 0){
							$record = $db->query("INSERT INTO participations (room_token, date_passage, user_id, cours_id, produit_adherent_id, status)
												VALUES('$room_token', '$cours_start', '$user_id', '$cours_id', '$produit_adherent_id', '2')");
							$insertedLines++;
						} else {
							$record = $db->query("UPDATE participations SET produit_adherent_id = '$produit_adherent_id' WHERE user_id = '$user_id' AND cours_id = '$cours_id'");
						}
						$delete = $db->query("DELETE FROM old_cours_participants WHERE id = '$current_id'");
					}
					echo $insertedLines." lignes insérées";*/
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
