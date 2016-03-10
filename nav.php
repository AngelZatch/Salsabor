<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$locationsNotif = $db->query("SELECT * FROM reservations WHERE paiement_effectue=0 AND priorite=1")->rowCount();

$queryPassages = $db->query("SELECT * FROM passages JOIN users ON passage_eleve=users.user_rfid");
?>

<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header"><a href="dashboard" class="navbar-brand"><img src="assets/images/logotest.png" alt="Salsabor Gestion" style="height:100%;"></a></div>
		<form action="search.php" class="navbar-form navbar-left" role="search">
			<div class="form-group">
				<input type="text" class="form-control" name="search_terms" placeholder="Chercher un utilisateur, cours, achat...">
			</div>
			<button type="submit" class="btn btn-default">Rechercher</button>
		</form>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
				<!--<li><a href=""><span class="glyphicon glyphicon-user"></span> ver.1.3.2</a></li>-->
				<li class="notification-option" title="Passages en attente de traitement">
					<a href="passages.php" class="notification-icon">
						<span class="glyphicon glyphicon-map-marker"></span>
						<span class="badge" id="badge-passages"></span>
					</a>
				</li>
				<li class="notification-option" title="Participants à un cours sans forfait">
					<a href="regularisation" class="notification-icon">
						<span class="glyphicon glyphicon-ice-lolly-tasted"></span>
						<span class="badge" id="badge-participants"></span>
					</a>
				</li>
				<li class="notification-option" title="Echéances en retard">
					<a href="echeances.php" class="notification-icon">
						<span class="glyphicon glyphicon-repeat"></span>
						<span class="badge" id="badge-echeances"></span>
					</a>
				</li>
				<li class="notification-option" title="Panier en cours">
					<a href="#" class="notification-icon" data-toggle="popover-x" data-target="#popoverPanier" data-trigger="focus" data-placement="bottom bottom-right">
						<span class="glyphicon glyphicon-shopping-cart"></span>
						<span class="badge" id="badge-panier"></span>
					</a>
					<div class="popover popover-default popover-md" id="popoverPanier">
						<div class="arrow"></div>
						<div class="popover-title"><span class="close" data-dismiss="popover-x">&times;</span>Panier en cours</div>
						<div class="popover-content">
							<table class="table-panier">
							</table>
						</div>
						<div class="popover-footer">
							<a href="" class="btn btn-success btn-block" role="button" name="next">Valider les achats</a>
						</div>
					</div>
				</li>
				<!--<li><a href=""><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>-->
			</ul>
		</div>
	</div>
</nav>
