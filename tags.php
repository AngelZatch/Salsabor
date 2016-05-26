<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>&Eacute;tiquettes | Salsabor</title>
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/tags.js"></script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-tags"></span> &Eacute;tiquettes</legend>
					<!--<ul class="nav nav-tabs">
						<li role="presentation" class="active"><a href="tags/users">Utilisateurs</a></li>
						<li role="presentation"><a href="tags/products">Produits</a></li>
					</ul>-->
					<p class="sub-legend">Liste des tags utilisateurs</p>
					<div class="tags-container"></div>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$.when(fetchUserTags()).done(function(data){
				var tags = JSON.parse(data), body = "<h4>";
				for(var i = 0; i < tags.length; i++){
					body += "<span class='label col-xs-7 label-clickable label-restyle' id='tag-"+tags[i].rank_id+"' data-tag='"+tags[i].rank_id+"' style='background-color:"+tags[i].color+"'>"+tags[i].rank_name+"</span>";
					body += "<span class='glyphicon glyphicon-pencil glyphicon-button glyphicon-button-alt col-xs-1 trigger-sub' id='edit-"+tags[i].rank_id+"' data-subtype='edit-tag' data-target='"+tags[i].rank_id+"' title='Editer l&apos;étiquette'></span>";
				}
				body += "</h4>";
				$(".tags-container").append(body);
			})
		</script>
	</body>
</html>
