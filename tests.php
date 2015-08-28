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
						<input type="text" name="identite">
						<input type="submit">
					</form>
					<?php

	$data = explode(' ', $_POST["identite"]);
$prenom = $data[0];
$nom = '';
for($i = 1; $i < count($data); $i++){
	$nom .= $data[$i];
	if($i != count($data)){
		$nom .= " ";
	}
}
echo $nom;

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
