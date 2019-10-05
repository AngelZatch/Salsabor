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
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script src="assets/js/participations.js"></script>
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
						<li role="presentation" class="active"><a href="user_subscriptions.php?id=<?php echo $user_id;?>">Abonnements</a></li>
						<li role="presentation"><a href="user_history.php?id=<?php echo $user_id;?>">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $user_id;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user_reservations.php?id=<?php echo $user_id;?>">Réservations</a></li>
						<li role="presentation"><a href="user_tasks.php?id=<?php echo $user_id;?>">Tâches</a></li>
					</ul>
					<div class="container-fluid purchase-product-list-container">
						<ul class="purchase-inside-list purchase-product-list loading-container"></ul>
					</div>
					<a href="catalogue.php?user=<?php echo $details["user_id"];?>" class="btn btn-primary btn-block">Acheter un nouveau produit pour cet adhérent</a>
				</div>
			</div>
		</div>
		<?php include "inserts/modal_product.php";?>
		<?php include "inserts/sub_modal_product.php";?>
		<?php include "inserts/edit_modal.php";?>
		<script>
			$(document).ready(function(){
				$(".purchase-product-list").trigger('loading');
				var token = {};
				token["user_id"] = /([0-9]+)/.exec(top.location.pathname)[0];
				$.when(fetchProducts($.param(token))).done(function(data){
					var construct = renderProductBanners(JSON.parse(data));
					$(".purchase-product-list").trigger('loaded');
					$(".purchase-product-list").append(construct);
				})
			})
		</script>
	</body>
</html>
