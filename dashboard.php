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
	$time_message = array(
		"Vous êtes là tôt aujourd'hui ! Bonjour !",
		"J'entends les oiseaux dehors... Bonjour !",
		"C'est une belle journée aujourd'hui. Les oiseaux chantent, les fleurs éclosent...",
		"Bonjour !"
	);
} else if($date > "10:00:00" && $date <= "12:00:00"){
	$time_message = array(
		"Bonjour !",
		"Un matin tranquille... Bonjour !",
		"Il fait un peu frais, non ?",
		"Pas de panne de réveil ? Bonjour !"
	);
} else if($date > "12:00:00" && $date <= "13:30:00") {
	$welcome = "N'oubliez pas de prendre des pauses... Bonjour !";
	$time_message = array(
		"Bonjour !",
		"N'oubliez pas de prendre des pauses... Bonjour !",
		"Vous devriez aller manger si ce n'est pas déjà le cas. Bonjour !",
		"Salade de fruits, jolie, jolie...",
		"J'ai faim. Et vous ?"
	);
} else if($date > "13:30:00" && $date <= "18:00:00") {
	$time_message = array(
		"Du travail vous attend ? Bon courage !",
		"Longue après-midi en perspective ? Bonjour !",
		"Bonjour !",
		"Vous êtes plutôt thé ou café ?"
	);
} else if($date > "18:00:00" && $date <= "21:00:00") {
	$time_message = array(
		"Bonsoir !",
		"Pas de panique, votre application est là !",
		"Je me demande bien combien de fois vous avez lu ce message...",
		"Bonjour ? Ah non. C'est bonsoir je crois.",
		"This is the rhythm of the night!"
	);
} else if($date > "21:00:00" && $date <= "23:00:00") {
	$time_message = array(
		"Courage, c'est bientôt fini !",
		"Plus que quelques heures !",
		"Alors c'est l'histoire de Toto qui... En fait peut-être pas.",
		"J'espère que vous n'êtes pas là depuis 5 heures ce matin..."
	);
} else {
	$time_message = array(
		"Bienvenue, M. Wayne.",
		"Sérieusement, vous avez vu l'heure ?!",
		"Pas de répit pour les héros... Bonsoir !",
		"Bonsoir ou Bonjour ? Je vous laisse décider, vu l'heure."
	);
}
$rand = rand(0, sizeof($time_message) - 1);
$welcome = $time_message[$rand];
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Accueil d'administration | Salsabor</title>
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/tasks-js.php"></script>
		<script src="assets/js/tags.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main-home">
					<div class="jumbotron jumbotron-home">
						<h1><?php echo $welcome;?></h1>
						<p>Bienvenue sur Salsabor Gestion ! Que souhaitez-vous faire ?</p>
						<p id="jumbotron-btns">
							<a class="btn btn-primary btn-lg" href="inscription"><span class="glyphicon glyphicon-user"></span> Inscription</a>
							<a class="btn btn-primary btn-lg" href="vente"><span class="glyphicon glyphicon-th"></span> Vente</a>
							<a href="participations" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-map-marker"></span> Passages</a>
							<a href="echeances" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-repeat"></span> &Eacute;chéances</a>
						</p>
					</div>
					<div class="col-lg-6 dashboard-zones clearfix container-fluid">
						<p class="sub-legend">Récemment...</p>
						<ul class="notifications-container container-fluid"></ul>
					</div>
					<div class="col-lg-6 dashboard-zones clearfix container-fluid">
						<p class="sub-legend">Il vous reste à faire...</p>
						<div class="tasks-container container-fluid"></div>
					</div>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$(document).ready(function(){
				moment.locale('fr');
				fetchNotifications(0);
				fetchTasks(0, "pending", 0);
			})
		</script>
	</body>
</html>
