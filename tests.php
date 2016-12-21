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
					$today = date("Y-m-d H:i:s");
					$reader_token = "192.168.0.4";
					$user_tag = "123457";
					$values = array(
						"passage_date" => $today,
						"room_token" => $reader_token,
						"user_rfid" => $user_tag
					);
					addParticipationTest($values);

					?>
					<pre>
						<?php
function addParticipationTest($values){
	$db = PDOFactory::getConnection();
	if(!isset($values["user_id"])){
		echo "No ID, finding...<br>";
		// We try to find the user from the details
		$user_id = $db->query("SELECT user_id FROM users WHERE user_rfid = '$values[user_rfid]'")->fetch(PDO::FETCH_COLUMN);
	} else {
		$user_id = $values["user_id"];
	}

	if(!isset($values["session_id"])){
		// We try to find the session
		$session_id = $db->query("SELECT session_id FROM sessions s
								JOIN rooms r ON s.session_room = r.room_id
								JOIN readers re ON r.room_reader = re.reader_id
								WHERE session_opened = '1' AND reader_token = '$values[room_token]'")->fetch(PDO::FETCH_COLUMN);

		if($session_id != "" || $session_id != NULL)
			$values["session_id"] = $session_id;
	} else {
		$session_id = $values["session_id"];
	}

	// We create the array of values the system will find
	//$duplicate_test = $db->query("SELECT COUNT(passage_id) FROM participations WHERE (user_rfid = '$values[user_rfid]' OR user_id = $values[user_id]) AND session_id = $values[session_id]")->fetch(PDO::FETCH_COLUMN);

	/*if($duplicate_test == 0){*/
	if($user_id != "" || $user_id != NULL){
		$values["user_id"] = $user_id;
		if($session_id != "" || $session_id != NULL){
			$product_id = getCorrectProductFromTags($session_id, $user_id) or NULL;
			if($product_id != "") $status = 0; // Product found.
			else $status = 3; // No product available
			$values["produit_adherent_id"] = $product_id;
		} else {
			$status = 4; // No session has been found
		}
	} else {
		$status = 5; // No user ID has been matched
	}

	$values["status"] = $status;

	print_r($values);

	require_once "functions/add_entry.php";
	addEntry("participations", $values);
	/*}*/

	echo "$";
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
