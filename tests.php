<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
include 'functions/reservations.php';
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template - Salsabor</title>
		<?php include "includes.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-10 main">
					<h1 class="page-title"><span class="glyphicon glyphicon-pencil"></span> Page Test !</h1>
					<form action="tests.php" method="post">
						<input type="text" name="produit">
						<input type="submit">
					</form>
					<?php
$articlePanier = $_GET["element"];
$elementSepare = explode('-', $articlePanier);
$panierTotal = array();
for($i = 0; $i < sizeof($elementSepare); $i++){
	echo $elementSepare[$i]."<br>";
	$elementPanier = $db->query("SELECT * FROM produits WHERE produit_id=$elementSepare[$i]");
	array_push($panierTotal, $elementPanier->fetch(PDO::FETCH_ASSOC));
}
print_r($panierTotal);
					?>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$('button').click(function(){
				var id;
				id = '#'+$(this).attr('id');
				console.log(id);
				$('#add-options').popoverX({
					target: id,
					placement: 'bottom',
					closeOtherPopovers: true,
				});
				$('#add-options').popoverX('toggle');
				$('#add-options').on('show.bs.modal', function(){
					$('#add-options').popoverX('refreshPosition');
				});
			})
		</script>
	</body>
</html>
<script>
</script>
