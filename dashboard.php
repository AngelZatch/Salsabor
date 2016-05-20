<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
include "functions/db_connect.php";
$db = PDOFactory::getConnection();

$date = date_create('now')->format('H:i:s');
$welcome = "";
if($date > "06:00:00" && $date <= "10:00:00"){
	$welcome = "Vous êtes là tôt aujourd'hui... Bonjour !";
} else if($date > "10:00:00" && $date <= "12:00:00"){
	$welcome = "Un matin tranquille... Bonjour !";
} else if($date > "12:00:00" && $date <= "13:30:00") {
	$welcome = "N'oubliez pas de prendre des pauses... Bonjour !";
} else if($date > "13:30:00" && $date <= "18:00:00") {
	$welcome = "Bonjour !";
} else if($date > "18:00:00" && $date <= "21:00:00") {
	$welcome = "Bonsoir !";
} else if($date > "21:00:00" && $date <= "23:00:00") {
	$welcome = "Courage, c'est bientôt fini !";
} else {
	$welcome = "Il fait nuit... Vous êtes toujours là ? Bienvenue !";
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Accueil d'administration | Salsabor</title>
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main-home">
					<div class="jumbotron jumbotron-home">
						<h1><?php echo $welcome;?></h1>
						<p>Bienvenue sur Salsabor Gestion ! Que souhaitez-vous faire ?</p>
						<p id="jumbotron-btns">
							<a class="btn btn-primary btn-lg" href="inscription"><span class="glyphicon glyphicon-user"></span> Réaliser une inscription</a>
							<a class="btn btn-primary btn-lg" href="vente"><span class="glyphicon glyphicon-th"></span> Vendre un produit</a>
							<a href="invitation" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-heart-empty"></span> Inviter un élève</a>
							<a href="participations" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-map-marker"></span> Consulter les passages</a>
							<a href="echeances" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-repeat"></span> Consulter les échéances</a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
