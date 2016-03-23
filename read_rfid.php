<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

if(isset($_GET["carte"])){
	$data = explode('*', $_GET["carte"]);
	$tag_rfid = $data[0];
	$ip_rfid = $data[1];
	add($tag_rfid, $ip_rfid);
}

if(isset($_POST["add"])){
	$tag_rfid = $_POST["tag"];
	$ip_rfid = $_POST["salle"];
	add($tag_rfid, $ip_rfid);
}

function add($tag, $ip){
	$db = PDOFactory::getConnection();
	$today = date_create('now')->format('Y-m-d H:i:s');

	if($ip == "192.168.0.3"){
		$status = "1";
	} else {
		$search = $db->query("SELECT * FROM users JOIN produits_adherents ON user_id=produits_adherents.id_user_foreign WHERE user_rfid='$tag'");
		$res = $search->fetch(PDO::FETCH_ASSOC);
		if($search->rowCount() == 0 || $res["date_expiration"] <= $today){
			$status = "3";
		} else {
			$status = "0";
		}
	}

	$new = $db->prepare("INSERT INTO passages(passage_eleve, passage_salle, passage_date, status)
	VALUE(:tag, :salle, :date, :status)");
	$new->bindParam(':tag', $tag);
	$new->bindParam(':salle', $ip);
	$new->bindParam(':date', $today);
	$new->bindParam(':status', $status);
	$new->execute();

	header('Location: passages.php');
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
					<p class="page-title"><span class="glyphicon glyphicon-qrcode"></span> RFID</p>
					<p>Simulez un passage RFID</p>
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
