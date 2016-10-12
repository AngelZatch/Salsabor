<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$searchTerms = $_GET["search_terms"];

$search_query = "SELECT user_id, CONCAT(user_prenom, ' ', user_nom) AS identity, mail, telephone, photo, actif, archived FROM users WHERE (user_nom LIKE ? OR user_prenom LIKE ? OR mail LIKE ? OR telephone LIKE ?)";

if(!isset($_GET["archive"]) || $_GET["archive"] == "0")
	$search_query .= " AND archived = 0";

$search_query .= " ORDER BY archived ASC, actif DESC, user_nom ASC, user_prenom ASC";
$searchUsers = $db->prepare($search_query);

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
						<?php if(!isset($_GET["archive"]) || $_GET["archive"] == "0"){ ?>
						<a href="search.php?search_terms=<?php echo $searchTerms;?>&archive=1" class="btn btn-primary float-right">Inclure les résultats archivés</a>
						<?php } else { ?>
						<a href="search.php?search_terms=<?php echo $searchTerms;?>&archive=0" class="btn btn-primary float-right">Exclure les résultats archivés</a>
						<?php } ?>
					</p>
					<div class="row">
						<?php while ($users = $searchUsers->fetch(PDO::FETCH_ASSOC)){
	if($users["archived"] == 1){
		$archived_class = "user-archived";
	} else {
		$archived_class = "";
	}?>
						<div class="col-md-6 col-lg-4">
							<div class="panel panel-search <?php echo $archived_class;?>">
								<div class="panel-body user-entry" title="<?php echo $users["identity"];?>">
									<a href="user/<?php echo $users["user_id"];?>">
										<div class="col-lg-4 col-md-3">
											<div class="small-user-pp visible-lg-block">
												<img src="<?php echo $users["photo"];?>" alt="<?php echo $users["identity"];?>">
											</div>
											<div class="notif-pp hidden-lg">
												<img src="<?php echo $users["photo"];?>" alt="<?php echo $users["identity"];?>">
											</div>
										</div>
										<div class="col-lg-8 col-md-9">
											<p class="panel-item-title bf"><?php echo $users["identity"];?></p>
											<p>
												<?php if($users["actif"] == 1){ ?>
												<span class="label label-success">Actif</span>
												<?php } else {  ?>
												<span class="label label-danger">Inactif</span>
												<?php } ?>
											</p>

											<p>
												<span class="glyphicon glyphicon-envelope"></span> <?php echo ($users["mail"]!=null)?$users["mail"]:"-";?>
											</p>
											<p>
												<span class="glyphicon glyphicon-phone"></span> <?php echo ($users["telephone"])?$users["telephone"]:"-";?>
											</p>
										</div>
									</a>
								</div>
							</div>
						</div>
						<?php }?>
					</div>
					<p class="search-title">
						<span class="glyphicon glyphicon-piggy-bank"></span> <?php echo $numberTransactions;?> transaction(s) correspond(ent) à votre recherche
					</p>
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
		<?php include "scripts.php";?>
	</body>
</html>
