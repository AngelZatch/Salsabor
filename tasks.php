<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$is_super_admin = $db->query("SELECT * FROM assoc_user_tags ur
								JOIN tags_user tu ON tu.rank_id = ur.tag_id_foreign
								WHERE rank_name = 'Super Admin' AND user_id_foreign = '$_SESSION[user_id]'")->rowCount();
$display = $_GET["display"];
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Tâches | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/tasks-js.php"></script>
		<script src="assets/js/tags.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-list-alt"></span> Tâches à faire</legend>
					<?php if($is_super_admin){;?>
					<ul class="nav nav-tabs">
						<li role="presentation" <?php if($display == "user") echo "class='active'";?>>
							<a href="taches/user">Vos tâches</a>
						</li>
						<li role="presentation" <?php if($display == "all") echo "class='active'";?>>
							<a href="taches/all">Totues les tâches</a>
						</li>
					</ul>
					<?php }?>
					<div class="tasks-container container-fluid"></div>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$(document).ready(function(){
				moment.locale('fr');
				<?php if($display == "user") {;?>
				fetchTasks(0, <?php echo $_SESSION["user_id"];?>, null, 0);
				<?php } else {;?>
				fetchTasks(0, 0, null, 0);
				<?php };?>
			})
		</script>
	</body>
</html>
<script>
</script>
