<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$searchTerms = $_GET["search_terms"];

$searchUsers = $db->query("SELECT user_id, user_prenom, user_nom, mail, telephone, actif FROM users WHERE user_nom LIKE '%{$searchTerms}%' OR user_prenom LIKE '%{$searchTerms}%' AND est_professeur = 0 AND est_staff = 0 ORDER BY user_nom ASC");
$numberUsers = $searchUsers->rowCount();

$searchProfs = $db->query("SELECT user_id, user_prenom, user_nom, mail, telephone FROM users WHERE user_nom LIKE '%{$searchTerms}%' OR user_prenom LIKE '%{$searchTerms}%' AND est_professeur = 1 ORDER BY user_nom ASC");
$numberProfs = $searchProfs->rowCount();

$searchStaff = $db->query("SELECT user_id, user_prenom, user_nom, mail, telephone FROM users WHERE user_nom LIKE '%{$searchTerms}%' OR user_prenom LIKE '%{$searchTerms}%' AND est_staff = 1 ORDER BY user_nom ASC");
$numberStaff = $searchStaff->rowCount();

$searchCours = $db->query("SELECT * FROM cours
							JOIN salle ON cours_salle=salle.salle_id
							JOIN prestations ON cours_type=prestations.prestations_id
							JOIN users ON prof_principal=users.user_id
							JOIN niveau ON cours_niveau=niveau.niveau_id
							WHERE cours_intitule LIKE '%{$searchTerms}%'");
$numberCours = $searchCours->rowCount();

$searchTransactions = $db->query("SELECT * FROM transactions WHERE id_transaction LIKE '%{$searchTerms}%'");
$numberTransactions = $searchTransactions->rowCount();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Résultats de recherche | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-search"></span> Résultats de recherche</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-sm-10 main">
					<div class="panel panel-default">
						<div class="panel-heading">
							<p class="panel-title">
								<span class="glyphicon glyphicon-user"></span> <?php echo $numberUsers;?> utilisateur(s) correspond(ent) à votre recherche
							</p>
						</div>
						<div class="list-group">
							<?php while ($users = $searchUsers->fetch(PDO::FETCH_ASSOC)){ ?>
							<a href="user_details.php?id=<?php echo $users["user_id"];?>&status=membre" class="list-group-item">
								<div class="row">
									<div class="col-lg-1">
										<?php if($users["actif"] == 1){ ?>
										<span class="glyphicon glyphicon-certificate glyphicon-success" title="Adhérent actif"></span>
										<?php } else {  ?>
										<span class="glyphicon glyphicon-certificate glyphicon-inactive" title="Adhérent inactif"></span>
										<?php } ?>
									</div>
									<div class="col-lg-4">
										<?php echo $users["user_prenom"]." ".$users["user_nom"];?>
									</div>
									<div class="col-lg-3">
										<?php echo $users["mail"];?>
									</div>
									<div class="col-lg-4">
										<?php echo $users["telephone"];?>
									</div>
								</div>
							</a>
							<?php }?>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<p class="panel-title">
								<span class="glyphicon glyphicon-blackboard"></span> <?php echo $numberProfs;?> professeur(s) correspond(ent) à votre recherche
							</p>
						</div>
						<div class="list-group">
							<?php while ($profs = $searchProfs->fetch(PDO::FETCH_ASSOC)){ ?>
							<a href="user_details.php?id=<?php echo $profs["user_id"];?>&status=membre" class="list-group-item">
								<div class="row">
									<div class="col-lg-4">
										<?php echo $profs["user_prenom"]." ".$profs["user_nom"];?>
									</div>
									<div class="col-lg-4">
										<?php echo $profs["mail"];?>
									</div>
									<div class="col-lg-4">
										<?php echo $profs["telephone"];?>
									</div>
								</div>
							</a>
							<?php }?>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<p class="panel-title">
								<span class="glyphicon glyphicon-briefcase"></span> <?php echo $numberStaff;?> staff correspond(ent) à votre recherche
							</p>
						</div>
						<div class="list-group">
							<?php while ($staff = $searchStaff->fetch(PDO::FETCH_ASSOC)){ ?>
							<a href="user_details.php?id=<?php echo $staff["user_id"];?>&status=membre" class="list-group-item">
								<div class="row">
									<div class="col-lg-4">
										<?php echo $staff["user_prenom"]." ".$staff["user_nom"];?>
									</div>
									<div class="col-lg-4">
										<?php echo $staff["mail"];?>
									</div>
									<div class="col-lg-4">
										<?php echo $staff["telephone"];?>
									</div>
								</div>
							</a>
							<?php }?>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<p class="panel-title"><span class="glyphicon glyphicon-eye-open"></span> <?php echo $numberCours;?> cours corresponde(nt) à votre recherche</p>
						</div>
						<div class="list-group">
							<?php while($cours = $searchCours->fetch(PDO::FETCH_ASSOC)){
	$dateCours = date_create($cours["cours_start"])->format('d/m/Y');
	$heureDebut = date_create($cours["cours_start"])->format('H:i');
	$heureFin = date_create($cours["cours_end"])->format('H:i');?>
							<a href="cours_edit.php?id=<?php echo $cours["cours_id"];?>" class="list-group-item">
								<div class="row">
									<div class="col-lg-2">
										<?php echo $cours["cours_intitule"];?>
									</div>
									<div class="col-lg-2">
										<?php echo $cours["prestations_name"];?><br>
										<span class="glyphicon glyphicon-signal"></span>
										<?php echo $cours["niveau_name"];?>
									</div>
									<div class="col-lg-3">
										<span class="glyphicon glyphicon-time"></span>
										<?php echo "Le ".$dateCours." de ".$heureDebut." à ".$heureFin;?>
									</div>
									<div class="col-lg-3">
										<span class="glyphicon glyphicon-pushpin"></span>
										<?php echo $cours["salle_name"];?>
									</div>
									<div class="col-lg-2">
										<span class="glyphicon glyphicon-blackboard"></span>
										<?php echo $cours["user_prenom"]." ".$cours["user_nom"];?>
									</div>
								</div>
							</a>
							<?php } ?>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<p class="panel-title"><span class="glyphicon glyphicon-piggy-bank"></span> <?php echo $numberTransactions;?> transaction(s) corresponde(nt) à votre recherche</p>
						</div>
						<div class="list-group">
							<?php while ($transaction = $searchTransactions->fetch(PDO::FETCH_ASSOC)){ ?>
							<a href="transaction_details.php?id=<?php echo $transaction["id_transaction"];?>&status=transactions" class="list-group-item">
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
