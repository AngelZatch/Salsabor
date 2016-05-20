<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$searchTerms = $_GET["search_terms"];

$searchUsers = $db->query("SELECT user_id, user_prenom, user_nom, mail, telephone, photo, actif FROM users WHERE (user_nom LIKE '%{$searchTerms}%' OR user_prenom LIKE '%{$searchTerms}%') AND est_professeur = 0 AND est_staff = 0 ORDER BY user_nom ASC");
$numberUsers = $searchUsers->rowCount();

$searchProfs = $db->query("SELECT user_id, user_prenom, user_nom, mail, telephone, photo, actif FROM users WHERE (user_nom LIKE '%{$searchTerms}%' OR user_prenom LIKE '%{$searchTerms}%') AND est_professeur = 1 ORDER BY user_nom ASC");
$numberProfs = $searchProfs->rowCount();

$searchStaff = $db->query("SELECT user_id, user_prenom, user_nom, mail, telephone FROM users WHERE (user_nom LIKE '%{$searchTerms}%' OR user_prenom LIKE '%{$searchTerms}%') AND est_staff = 1 ORDER BY user_nom ASC");
$numberStaff = $searchStaff->rowCount();

$searchTransactions = $db->query("SELECT * FROM transactions WHERE id_transaction LIKE '%{$searchTerms}%'");
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
				<div class="col-lg-10 col-lg-offset-2 main">
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
											<img src="<?php echo $users["photo"];?>" alt="" class="profile-picture">
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
					<p class="search-title">
						<span class="glyphicon glyphicon-blackboard"></span> <?php echo $numberProfs;?> professeur(s) correspond(ent) à votre recherche
					</p>
					<div class="container-fluid">
						<?php while ($profs = $searchProfs->fetch(PDO::FETCH_ASSOC)){ ?>
						<div class="col-lg-3">
							<div class="panel panel-search">
								<div class="panel-body user-entry">
									<a href="user/<?php echo $profs["user_id"];?>">
										<div class="user-pp">
											<img src="<?php echo $profs["photo"];?>" alt="" class="profile-picture">
										</div>
										<p>
											<?php if($profs["actif"] == 1){ ?>
											<span class="glyphicon glyphicon-certificate glyphicon-success" title="Adhérent actif"></span>
											<?php } else {  ?>
											<span class="glyphicon glyphicon-certificate glyphicon-inactive" title="Adhérent inactif"></span>
											<?php } ?>
											<?php echo $profs["user_prenom"]." ".$profs["user_nom"];?>
										</p>
										<p>
											<span class="glyphicon glyphicon-envelope"></span> <?php echo ($profs["mail"]!=null)?$profs["mail"]:"-";?>
										</p>
										<p>
											<span class="glyphicon glyphicon-phone"></span> <?php echo ($profs["telephone"])?$profs["telephone"]:"-";?>
										</p>
									</a>
								</div>
							</div>
						</div>
						<?php }?>
					</div>
					<p class="search-title">
						<span class="glyphicon glyphicon-briefcase"></span> <?php echo $numberStaff;?> staff correspond(ent) à votre recherche
					</p>
					<div class="container-fluid">
						<?php while ($staff = $searchStaff->fetch(PDO::FETCH_ASSOC)){ ?>
						<div class="col-lg-3">
							<div class="panel panel-search">
								<div class="panel-body user-entry">
									<a href="user/<?php echo $staff["user_id"];?>">
										<div class="user-pp">
											<img src="<?php echo $staff["photo"];?>" alt="" class="profile-picture">
										</div>
										<p>
											<?php if($staff["actif"] == 1){ ?>
											<span class="glyphicon glyphicon-certificate glyphicon-success" title="Adhérent actif"></span>
											<?php } else {  ?>
											<span class="glyphicon glyphicon-certificate glyphicon-inactive" title="Adhérent inactif"></span>
											<?php } ?>
											<?php echo $staff["user_prenom"]." ".$staff["user_nom"];?>
										</p>
										<p>
											<span class="glyphicon glyphicon-envelope"></span> <?php echo ($staff["mail"]!=null)?$staff["mail"]:"-";?>
										</p>
										<p>
											<span class="glyphicon glyphicon-phone"></span> <?php echo ($staff["telephone"])?$staff["telephone"]:"-";?>
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
