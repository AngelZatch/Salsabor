<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$locationsNotif = $db->query("SELECT * FROM reservations WHERE paiement_effectue=0 AND priorite=1")->rowCount();
?>

<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<a href="dashboard" class="navbar-brand"><img src="assets/images/logotest.png" alt="Salsabor Gestion" style="height:100%;"></a>
		</div>
		<div class="col-lg-7">
			<form action="search.php" class="navbar-form navbar-left" role="search">
				<div class="input-group">
					<span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
					<input type="text" class="form-control nav-input" name="search_terms" placeholder="Rechercher">
				</div>
				<!--<button type="submit" class="btn btn-default">Rechercher</button>-->
			</form>
		</div>
		<ul class="nav navbar-nav navbar-right">
			<li class="notification-option">
				<a class="notification-icon trigger-nav">
					<span class="glyphicon glyphicon-bell"></span>
					<span class="badge badge-notifications" id="badge-notifications"></span>
				</a>
			</li>
			<li class="notification-option">
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
			<li class="notification-option">
				<?php if(isset($_SESSION["username"])){ ?>
				<!--<a href="logout.php" class="notification-icon"><span class="glyphicon glyphicon-log-out"></span> Déconnexion</a>-->
				<a href="" class="notification-icon nav-img-container">
					<div class="nav-pp">
						<img src="<?php echo $_SESSION["photo"];?>" alt="" style="width:inherit;">
					</div>
				</a>
				<?php }?>
			</li>
			<!--<li><a href=""><span class="glyphicon glyphicon-off"></span> Déconnexion</a></li>-->
		</ul>
	</div>
</nav>
<?php include "inserts/sub_modal_notifications.php";?>
