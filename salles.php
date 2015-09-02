<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$querySalles = $db->query('SELECT * FROM salle WHERE est_salle_cours=1 ORDER BY salle_name ASC');
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Salles | Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-pushpin"></span> Salles</p>
					</div>
					<div class="col-lg-6">
						<div class="btn-toolbar">
							<a href="" role="button" class="btn btn-primary" disabled><span class="glyphicon glyphicon-plus"></span> Ajouter une salle</a>
						</div> <!-- btn-toolbar -->
					</div>
				</div>
				<div class="col-sm-10 main">
					<div class="input-group input-group-lg search-form">
						<span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span></span>
						<input type="text" id="search" class="form-control" placeholder="Tapez pour rechercher...">
					</div>
					<div id="rooms-list">
						<table class="table table-striped">
							<thead>
								<tr>
									<th class="col-lg-1"></th>
									<th class="col-lg-4">Nom <span class="glyphicon glyphicon-sort sort" data-sort="room-name"></span></th>
									<th class="col-lg-6">Adresse <span class="glyphicon glyphicon-sort sort" data-sort="adresse"></span></th>
									<th class="col-lg-1"></th>
								</tr>
							</thead>
							<tbody id="filter-enabled" class="list">
								<?php while($salles = $querySalles->fetch(PDO::FETCH_ASSOC)) {
	$queryCours = $db->prepare("SELECT * FROM cours WHERE ouvert=1 AND cours_salle=?");
	$queryCours->bindParam(1, $salles["salle_id"]);
	$queryCours->execute();
								?>
								<tr>
									<?php if($queryCours->rowCount() != 0){ ?>
									<td class="col-lg-1"><span class="glyphicon glyphicon-certificate glyphicon-danger" title="Un cours a lieu actuellement dans cette salle"></span></td>
									<?php } else { ?>
									<td class="col-lg-1"><span class="glyphicon glyphicon-certificate glyphicon-success" title="Salle disponible"></span></td>
									<?php } ?>
									<td class="col-lg-4 room-name"><?php echo $salles['salle_name'];?></td>
									<td class="col-lg-6 adresse"><?php echo $salles['salle_adresse'];?></td>
									<td class="col-lg-1"><a href="salles_details.php?id=<?php echo $salles["salle_id"];?>" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Détails...</a></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			var options = {
				valueNames: ['room-name', 'adresse']
			};
			var roomsList = new List('rooms-list', options);
		</script>
	</body>
</html>
