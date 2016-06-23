<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$searchTerms = $_GET["search_terms"];

$searchUsers = $db->prepare("SELECT user_id, user_prenom, user_nom, mail, telephone, photo, actif FROM users WHERE (user_nom LIKE ? OR user_prenom LIKE ? OR mail LIKE ? OR telephone LIKE ?) ORDER BY user_nom ASC");
$searchUsers->execute(array("%".$searchTerms."%", "%".$searchTerms."%", "%".$searchTerms."%", "%".$searchTerms."%"));
$numberUsers = $searchUsers->rowCount();

$searchTransactions = $db->prepare("SELECT * FROM transactions WHERE id_transaction LIKE ?");
$searchTransactions->execute(array("%".$searchTerms."%"));
$numberTransactions = $searchTransactions->rowCount();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Résultats de recherche | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend>Résultats de recherche</legend>
					<p class="search-title">
						<span class="glyphicon glyphicon-user"></span> <?php echo $numberUsers;?> utilisateur(s) correspond(ent) à votre recherche
					</p>
					<div class="container-fluid">
						<?php while ($users = $searchUsers->fetch(PDO::FETCH_ASSOC)){ ?>
						<div class="col-lg-3">
							<div class="panel panel-search">
								<div class="panel-body user-entry">
									<a href="user/<?php echo $users["user_id"];?>">
										<div class="user-pp">
											<img src="<?php echo $users["photo"];?>" alt="<?php echo $users["user_prenom"]." ".$users["user_nom"];?>" class="profile-picture">
										</div>
										<p>
											<?php if($users["actif"] == 1){ ?>
											<span class="glyphicon glyphicon-certificate glyphicon-success" title="Adhérent actif"></span>
											<?php } else {  ?>
											<span class="glyphicon glyphicon-certificate glyphicon-inactive" title="Adhérent inactif"></span>
											<?php } ?>
											<?php echo $users["user_prenom"]." ".$users["user_nom"];?>
										</p>
										<p>
											<span class="glyphicon glyphicon-envelope"></span> <?php echo ($users["mail"]!=null)?$users["mail"]:"-";?>
										</p>
										<p>
											<span class="glyphicon glyphicon-phone"></span> <?php echo ($users["telephone"])?$users["telephone"]:"-";?>
										</p>
									</a>
								</div>
							</div>
						</div>
						<?php }?>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<p class="panel-title"><span class="glyphicon glyphicon-piggy-bank"></span> <?php echo $numberTransactions;?> transaction(s) corresponde(nt) à votre recherche</p>
						</div>
						<div class="list-group">
							<?php while ($transaction = $searchTransactions->fetch(PDO::FETCH_ASSOC)){ ?>
							<a href="user/<?php echo $transaction["payeur_transaction"];?>/achats#purchase-<?php echo $transaction["id_transaction"];?>" class="list-group-item">
								<div class="row">
									<div class="col-lg-6">
										<?php echo $transaction["id_transaction"];?>
									</div>
								</div>
							</a>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
