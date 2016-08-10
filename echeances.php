<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$date = new DateTime('now');
$year = $date->format('Y');
$month = $date->format('m');
$day = $date->format('d');
if($day >= 1 && $day <= 8){
	$maturityDay = 10;
} else if($day >= 9 && $day <= 18){
	$maturityDay = 20;
} else if($day >= 19 && $day <= 28){
	$maturityDay = 30;
}else{
	$maturityDay = 10;
	$month+=1;
}
$time = new DateTime($year.'-'.$month.'-'.$maturityDay);
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Echeances | Salsabor</title>
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/maturities.js"></script>
		<script src="assets/js/list.min.js"></script>
		<script>
			$(document).ready(function(){
				$.get("functions/fetch_current_maturities.php").done(function(data){
					var maturities = JSON.parse(data);
					var display = displayMaturities(maturities);
					$(".maturities-list").append(display);
					$(".maturities-total-count").text($(".maturity-item").length);
					var total_price = 0;
					for(var i = 0; i < maturities.length; i++){
						total_price += parseFloat(maturities[i].price);
					}
					$(".total-value").text(total_price.toFixed(2));
				})

				setInterval(keepCounts, 5000);
				function keepCounts(){
					$(".maturities-received-count").text($(".status-partial-success").length + $(".status-success").length);
				}
			})
		</script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-repeat"></span> Echéances</legend>
					<div class="panel panel-purchase  maturities-container" id="maturities-list">
						<div class="panel-heading container-fluid">
							<p class="col-xs-5 col-md-4">Encaissement prévu le <?php echo $time->format('d/m/Y');?></p>
							<p class="col-xs-2 col-md-1"><span class="glyphicon glyphicon-repeat"></span> <span class="maturities-total-count">0</span></p>
							<p class="col-xs-2 col-md-1"><span class="glyphicon glyphicon-ok"></span> <span class="maturities-received-count">0</span></p>
							<p class="col-xs-3 col-md-2"><span class="glyphicon glyphicon-piggy-bank"></span> <span class="total-value">0</span> €</p>
							<span class="glyphicon glyphicon-download-alt col-xs-2 col-md-1 col-md-offset-3 glyphicon-button glyphicon-button-alt glyphicon-button-big bank-all" title="Marquer toutes les échéances comme encaissées"></span>
						</div>
						<div class="panel-body row">
							<ul class="purchase-inside-list maturities-list"></ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
	</body>
</html>
