<?php
require_once 'functions/db_connect.php';
include "functions/tools.php";
$db = PDOFactory::getConnection();

if(isset($_GET["carte"])){
	$data = explode('*', $_GET["carte"]);
	$tag_rfid = $data[0];
	$ip_rfid = $data[1];
	prepareRecord($db, $tag_rfid, $ip_rfid);
}

if(isset($_POST["add"])){
	$tag_rfid = $_POST["tag"];
	$ip_rfid = $_POST["salle"];
	prepareRecord($db, $tag_rfid, $ip_rfid);
}

function prepareRecord($db, $tag, $ip){
	if($ip == "192.168.0.3"){
		$status = "1";
	} else {
		// If the tag is not for associating, we search a product that could be used for this session.
		// First, we get the name of the session and the ID of the user.
		// For the session, we have to find it based on the time of the record and the position.
		$session = $db->query("SELECT cours_intitule, cours_id FROM cours c
								JOIN lecteurs_rfid lr ON c.cours_salle = lr.lecteur_lieu
								WHERE ouvert = '1' AND lecteur_ip = '$ip'")->fetch(PDO::FETCH_GROUP);
		$cours_name = $session["cours_intitule"];
		$session_id = $session["cours_id"];
		$user_id = $db->query("SELECT user_id FROM users WHERE user_rfid = '$tag'")->fetch(PDO::FETCH_COLUMN);

		addRecord($db, $cours_name, $session_id, $user_id, $ip, $tag);
	}
	header('Location: passages');
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
				<div class="col-lg-10 col-lg-offset-2 main">
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
