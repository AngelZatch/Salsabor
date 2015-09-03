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
				<div class="fixed">
					<div class="col-lg-6">
						<p class="page-title"><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</p>
					</div>
					<div class="col-lg-6"></div>
				</div>
				<div class="col-sm-10 main">
					<?php echo generateReference();?>
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
