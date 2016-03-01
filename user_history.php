<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// On obtient les détails de l'adhérent
$queryDetails = $db->prepare('SELECT * FROM users WHERE user_id=?');
$queryDetails->bindValue(1, $data);
$queryDetails->execute();
$details = $queryDetails->fetch(PDO::FETCH_ASSOC);

// On obtient l'historique de ses cours
$queryHistoryRecus = $db->prepare('SELECT * FROM cours_participants
							JOIN cours ON cours_id_foreign=cours.cours_id
							JOIN niveau ON cours.cours_niveau=niveau.niveau_id
							JOIN salle ON cours.cours_salle=salle.salle_id
							JOIN produits_adherents ON produit_adherent_id=produits_adherents.id_produit_adherent
							JOIN produits ON id_produit_foreign=produits.produit_id
							WHERE eleve_id_foreign=?');
$queryHistoryRecus->bindValue(1, $data);
$queryHistoryRecus->execute();
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
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-user"></span> <?php echo $details["user_prenom"]." ".$details["user_nom"];?> - Historique de cours</p>
					</div>
				</div>
				<div class="col-sm-10 main">
					<ul class="nav nav-tabs">
						<li role="presentation"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/historique">Cours suivis</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<?php if($details["est_professeur"] == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<section id="history-suivis">
						<table class="table table-striped">
							<thead>
								<tr>
									<th class="col-lg-2">Intitulé</th>
									<th class="col-lg-3">Jour</th>
									<th class="col-lg-2">Détails</th>
									<th class="col-lg-3">Forfait</th>
									<th class="col-lg-2">Prix pondéré</th>
								</tr>
							</thead>
							<tbody>
								<?php while($history = $queryHistoryRecus->fetch(PDO::FETCH_ASSOC)){ ?>
								<tr <?php echo ($history["produit_adherent_id"]==null)?"class='warning'":"";?>>
									<td class="col-lg-2"><?php echo $history['cours_intitule']." ".$history['cours_suffixe'];?></td>
									<td class="col-lg-3"><?php echo date_create($history['cours_start'])->format('d/m/Y H:i');?> - <?php echo date_create($history['cours_end'])->format('H:i');?></td>
									<td class="col-lg-2"><?php echo $history['niveau_name']."\n".$history['salle_name'];?></td>
									<td class="col-lg-3">
										<?php if($history["produit_adherent_id"]==null){?>
										<a class="btn btn-info" name="link-forfait"><span class="glyphicon glyphicon-link"></span> Associer un forfait</a>
										<input type="hidden" name="cours" value="<?php echo $history["cours_id"];?>">
										<select name="forfaits-actifs" style="display:none;" class="form-control">
											<?php while($forfaitsActifs = $queryForfaitsActifs->fetch(PDO::FETCH_ASSOC)){?>
											<option value="<?php echo $forfaitsActifs["id_transaction"]?>"><?php echo $forfaitsActifs["produit_nom"];?></option>
											<?php } ?>
										</select>
										<?php } else echo $history["produit_nom"];?>
									</td>
									<td class="col-lg-2">A déterminer</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</section>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
