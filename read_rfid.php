<?php
session_start();
require_once 'functions/db_connect.php';
include "functions/tools.php";
$db = PDOFactory::getConnection();

if(isset($_GET["carte"])){
	$data = explode('*', $_GET["carte"]);
	$tag_rfid = $data[0];
	$reader_token = $data[1];
	prepareParticipation($db, $tag_rfid, $reader_token);
}

if(isset($_POST["add"])){
	$tag_rfid = $_POST["tag"];
	$reader_token = $_POST["salle"];
	prepareParticipation($db, $tag_rfid, $reader_token);
}

function prepareParticipation($db, $user_tag, $reader_token){
	$today = date("Y-m-d H:i:s");
	if($reader_token == "192.168.0.3"){
		$status = "1";
		$new = $db->query("INSERT INTO participations(user_rfid, room_token, passage_date, status)
					VALUES('$user_tag', '$reader_token', '$today', '$status')");
		echo "$";
	} else {
		// If the tag is not for associating, we search a product that could be used for this session.
		// First, we get the name of the session and the ID of the user.
		// For the session, we have to find it based on the time of the record and the position.
		$session_id = $db->query("SELECT cours_id FROM cours c
								JOIN rooms r ON c.cours_salle = r.room_id
								WHERE ouvert = '1' AND room_reader = '$reader_token'")->fetch(PDO::FETCH_COLUMN);
		$user_id = $db->query("SELECT user_id FROM users WHERE user_rfid = '$user_tag'")->fetch(PDO::FETCH_COLUMN);

		addParticipationBeta($db, $today, $session_id, $user_id, $reader_token, $user_tag);
		/*addParticipation($db, $cours_name, $session_id, $user_details["user_id"], $reader_token, $tag);*/
	}
	/*header('Location: passages');*/
}
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
					<legend><span class="glyphicon glyphicon-qrcode"></span> Simuler un passage RFID</legend>
					<p class="page-title"></p>
					<form action="" method="post">
						<label for="tag">Tag</label>
						<input type="text" name="tag" class="form-control">

						<label for="salle">Salle du lecteur</label>
						<input type="text" name="salle" class="form-control">

						<input type="submit" value="SIMULER UN PASSAGE" name="add" class="btn btn-primary confirm-add">
					</form>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
