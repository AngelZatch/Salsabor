<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Notifications | Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-bell"></span> Notifications</legend>
					<ul class="notifications-container">

					</ul>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).ready(function(){
				moment.locale('fr');
				$.get("functions/fetch_notifications.php", {limit : "null"}).done(function(data){
					var notifications = JSON.parse(data);
					for(var i = 0; i < notifications.length; i++){
						// Status handling
						var notifMessage = "", notifClass = "";
						if(notifications[i].status == '1'){
							notifClass = "notif-new";
						} else {
							notifClass = "notif-old";
						}
						notifMessage += "<li class='notification-line "+notifClass+"'";

						// Token handling
						switch(notifications[i].type){
							case "PRD":
								notifMessage += "onclick=window.location='user/"+notifications[i].user_id+"/abonnements'>";
								notifMessage += "<div class='notif-pp'><image src='"+notifications[i].photo+"'></div>";
								switch(notifications[i].subtype){
									case "NE":
										notifMessage += "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" arrivera à expiration le <strong>"+moment(notifications[i].product_validity).format("DD/MM/YYYY")+"</strong>.";
										break;

									case "NH":
										notifMessage += "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" n'a plus que "+notifications[i].remaining_hours+" heures restantes.";
										break;

									case "E":
										notifMessage += "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" a expiré le "+notifications[i].product_usage+".";
										break;
								}
								notifMessage += "</p><p class='notif-hour'><span class='glyphicon glyphicon-credit-card'></span> ";
								break;

							case "MAT":
								notifMessage += "onclick=window.location='user/"+notifications[i].user_id+"/achats#purchase-"+notifications[i].transaction+"'>";
								notifMessage += "<div class='notif-pp'><image src='"+notifications[i].photo+"'></div>";
								switch(notifications[i].subtype){
									case "NE":
										notifMessage += "L'échéance de <strong>"+notifications[i].payer+"</strong> pour "+notifications[i].maturity_value+" € de la transaction "+notifications[i].transaction+" arrive à sa date limite, fixée au <strong>"+moment(notifications[i].maturity_date).format("DD/MM/YYYY")+"</strong>.";
										break;

									case "E":
										notifMessage += "L'échéance de <strong>"+notifications[i].payer+"</strong> pour "+notifications[i].maturity_value+" € de la transaction "+notifications[i].transaction+" prévue pour le  "+moment(notifications[i].maturity_date).format("DD/MM/YYYY")+" a expiré.";
										break;

									case "L":
										notifMessage += "L'échéance de <strong>"+notifications[i].payer+"</strong> pour "+notifications[i].maturity_value+" € de la transaction "+notifications[i].transaction+" prévue pour le  "+moment(notifications[i].maturity_date).format("DD/MM/YYYY")+" <strong>est en retard</strong>.";
										break;
								}
								notifMessage += "</p><p class='notif-hour'><span class='glyphicon glyphicon-repeat'></span> ";
								break;

							case "TRA":
								notifMessage += ">";
								notifMessage += "<div class='notif-pp'><image src='"+notifications[i].photo+"'></div>";
								switch(notifications[i].subtype){
									case "NE":
										break;

									case "E":
										break;

									case "L":
										break;
								}
								notifMessage += "</p><p class='notif-hour'><span class='glyphicon glyphicon-mail'></span> ";
								break;


							case "MAI":
								notifMessage += "onclick=window.location='user/"+notifications[i].user_id+"'>";
								notifMessage += "<div class='notif-pp'><image src='"+notifications[i].photo+"'></div>";
								notifMessage += "<strong>"+notifications[i].user+"</strong> n'a pas d'adresse mail enregistrée.";
								notifMessage += "</p><p class='notif-hour'><span class='glyphicon glyphicon-envelope'></span> ";
								break;

							default:
								break;
						}
						notifMessage += ""+moment(notifications[i].date).fromNow();
						notifMessage += "</li>";

						$(".notifications-container").append(notifMessage);
					}
				})
			})
		</script>
	</body>
</html>
<script>
</script>
