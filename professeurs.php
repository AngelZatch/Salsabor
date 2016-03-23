<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Professeurs | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-blackboard"></span> Base Professeurs</p>
					</div>
					<div class="col-lg-6">
						<div class="btn-toolbar">
							<a href="inscription.php?status=teacher" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Inscrire un professeur</a>
						</div> <!-- btn-toolbar -->
					</div>
				</div>
				<div class="col-lg-10 col-lg-offset-2 main">
					<div class="input-group input-group-lg search-form">
						<span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span></span>
						<input type="text" id="search" class="form-control" placeholder="Tapez pour rechercher...">
					</div>
					<?php
$profs = $db->query('SELECT * FROM users WHERE est_professeur=1');
					?>
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th class="col-sm-4">Professeur</th>
									<th class="col-sm-3"></th>
								</tr>
							</thead>
							<tbody id="filter-enabled">
								<?php
while($row_profs = $profs->fetch(PDO::FETCH_ASSOC)){
								?>
								<tr>
									<td class="col-sm-4">
										<?php
	echo $row_profs['user_prenom'].' '.$row_profs['user_nom'];
										?>
									</td>
									<td class="col-sm-3">
										<a href="user_details.php?id=<?php echo $row_profs['user_id'];?>&status=professeur" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Détails...</a>
									</td>
								</tr>
								<?php
}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
