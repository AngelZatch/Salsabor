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
					<div class="tags-container col-sm-12"></div>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
		<script>
			$.when(fetchUserTags()).done(function(data){
				var tags = JSON.parse(data), body = "";
				body += "<div class='col-sm-12'><p class='col-xs-4'>Etiquette</p><p class='col-xs-2'>Editer</p><p class='col-xs-4'>Tâches 'infos. manquantes'</p></div>";
				for(var i = 0; i < tags.length; i++){
					body += "<h4><div class='col-sm-12'><span class='label col-xs-4 label-clickable label-restyle' id='tag-"+tags[i].rank_id+"' data-tag='"+tags[i].rank_id+"' style='background-color:"+tags[i].color+"'>"+tags[i].rank_name+"</span>";

					body += "<p class='col-xs-2'><span class='glyphicon glyphicon-pencil glyphicon-button glyphicon-button-alt trigger-sub' id='edit-"+tags[i].rank_id+"' data-subtype='edit-tag' data-target='"+tags[i].rank_id+"' title='Editer l&apos;étiquette'></span></p>";

					body += "<p class='col-xs-4'><span class='glyphicon glyphicon-list-alt glyphicon-button glyphicon-button-alt mid-button";
					if(tags[i].mid == 0){
						body += " glyphicon-button-disabled";
					} else {
						body += " glyphicon-button-enabled";
					}
					body += "' id='mid-"+tags[i].rank_id+"' data-target='"+tags[i].rank_id+"' title='Indiquer l&apos;étiquette comme celle par défaut pour les tâches de type &apos;Informations manquantes&apos;'></span></p></div></h4>";
				}
				body += "<h4><div class='col-sm-12'><span class='label col-xs-4 label-default label-clickable label-new-tag'>Créer une étiquette</span></div></h4>";
				body += "";
				$(".tags-container").append(body);
			})
		</script>
	</body>
</html>
