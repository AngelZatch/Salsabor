<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal.php');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$user_id = $_GET['id'];

// User details
$details = $db->query("SELECT * FROM users u
						LEFT JOIN locations l ON u.user_location = l.location_id
						WHERE user_id='$user_id'")->fetch(PDO::FETCH_ASSOC);

$details["count"] = $db->query("SELECT * FROM tasks
					WHERE ((task_token LIKE '%USR%' AND task_target = '$user_id')
					OR (task_token LIKE '%PRD%' AND task_target IN (SELECT id_produit_adherent FROM produits_adherents WHERE id_user_foreign = '$user_id'))
					OR (task_token LIKE '%TRA%' AND task_target IN (SELECT id_transaction FROM transactions WHERE payeur_transaction = '$user_id')))
						AND task_state = 0")->rowCount();

// On obtient l'historique de ses réservations
$queryResa = $db->prepare('SELECT * FROM reservations b
							JOIN users u ON b.booking_holder = u.user_id
							JOIN rooms r ON b.booking_room = r.room_id
							WHERE booking_holder=?');
$queryResa->bindValue(1, $user_id);
$queryResa->execute();

$is_teacher = $db->query("SELECT * FROM assoc_user_tags ur
								JOIN tags_user tu ON tu.rank_id = ur.tag_id_foreign
								WHERE rank_name = 'Professeur' AND user_id_foreign = '$user_id'")->rowCount();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Editer - <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<ul class="nav nav-tabs">
						<li role="presentation" class="visible-xs-block"><a href="user/<?php echo $user_id;?>">Infos perso</a></li>
						<li role="presentation" class="hidden-xs"><a href="user/<?php echo $user_id;?>">Informations personnelles</a></li>
						<?php if($is_teacher == 1){ ?>
						<!--<li role="presentation"><a>Cours donnés</a></li>-->
						<li role="presentation"><a href="user_rates.php?id=<?php echo $user_id;?>">Tarifs</a></li>
						<li role="presentation"><a href="user_billing.php?id=<?php echo $user_id;?>">Facturation</a></li>
						<!--<li role="presentation"><a>Statistiques</a></li>-->
						<?php } ?>
						<li role="presentation"><a href="user_subscriptions.php?id=<?php echo $user_id;?>">Abonnements</a></li>
						<li role="presentation"><a href="user_history.php?id=<?php echo $user_id;?>">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $user_id;?>/achats">Achats</a></li>
						<li role="presentation" class="active"><a href="user_reservations.php?id=<?php echo $user_id;?>">Réservations</a></li>
						<li role="presentation"><a href="user_tasks.php?id=<?php echo $user_id;?>">Tâches</a></li>
					</ul>
					<section id="resa">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Plage horaire</th>
									<th>Lieu</th>
									<th>Activité</th>
									<th>Prix de la réservation</th>
								</tr>
							</thead>
							<tbody>
								<?php while($reservations = $queryResa->fetch(PDO::FETCH_ASSOC)){ ?>
								<tr>
									<td>Le <?php echo date_create($reservations["booking_start"])->format('d/m/Y \d\e H\hi');?> à <?php echo date_create($reservations["booking_end"])->format('H\hi');?></td>
									<td><?php echo $reservations["salle_name"];?></td>
									<td><?php echo $reservations["prestations_name"];?></td>
									<td><?php echo $reservations["booking_price"];?> €</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</section>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<?php include "inserts/edit_modal.php";?>
	</body>
</html>
