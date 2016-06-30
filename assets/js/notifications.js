moment.locale('fr');
$(document).on('click', '.trigger-nav', function(e){
	e.stopPropagation();
	if($(".sub-modal-notification").is(":visible")){
		$(".sub-modal-notification").hide(0);
	} else {
		fetchNotifications(50, null, "smn-body");
		$(".sub-modal-notification").css({left: 64+"%", top:55+"px"});
		$(".sub-modal-notification").show(0);
	}
}).on('click', '.smn-close', function(e){
	e.stopPropagation();
	$(".sub-modal-notification").hide(0);
}).on('click', '.toggle-read', function(e){
	e.stopImmediatePropagation();
	var notification_id = $(this).parents("li").data().notification;

	if($("#notification-"+notification_id).hasClass("notif-new")){
		var value = "0";
	} else {
		var value = "1";
	}

	$.when(updateColumn("team_notifications", "notification_state", value, notification_id)).done(function(){
		$("#notification-"+notification_id).removeClass("notif-old");
		$("#notification-"+notification_id).removeClass("notif-new");
		if(value == 1){
			$("#notification-"+notification_id).addClass("notif-new");
			var span = $("#notification-"+notification_id).find("span.glyphicon-button");
			span.replaceWith("<span class='glyphicon glyphicon-ok-circle col-sm-1 glyphicon-button toggle-read' title='Marquer comme lue'></span>");
			$(".badge-notifications").html(parseInt($("#badge-notifications").html())+1);
		} else {
			$("#notification-"+notification_id).addClass("notif-old");
			var span = $("#notification-"+notification_id).find("span.glyphicon-button");
			span.replaceWith("<span class='glyphicon glyphicon-ok-sign col-sm-1 glyphicon-button toggle-read' title='Marquer comme non lue'></span>");
			$(".badge-notifications").html(parseInt($("#badge-notifications").html())-1);
			if(top.location.pathname === "/Salsabor/dashboard"){
				$("#notification-"+notification_id).fadeOut('normal', function(){
					$(this).remove();
				});
			}
		}
	})
}).on('click', '.notification-line', function(){
	var notification_id = $(this).data().notification;
	if($("#notification-"+notification_id).hasClass("notif-new")){
		var notification_id = $(this).data().notification;
		updateColumn("team_notifications", "notification_state", 0, notification_id);
	}
	window.location = $(this).data().redirect;
}).on('click', '.read-all', function(e){
	e.stopPropagation();
	$.post("functions/read_all.php");
})

function fetchNotifications(limit, filter, destination){
	/*console.log(limit);*/
	$.get("functions/fetch_notifications.php", {filter : filter, limit : limit}).done(function(data){
		if($("."+destination).is(":visible")){
			displayNotifications(data, limit, filter, destination);
		}
	});
}

function displayNotifications(data, limit, filter, destination){
	var notifications = JSON.parse(data);
	$("."+destination).empty();
	if(destination == "dashboard-notifications-container"){
		if(notifications.length == 0){
			$(".dashboard-notifications-container").empty();
			$(".dashboard-notifications-container").css("background-image", "url(assets/images/logotype-white.png)");
			$(".dashboard-notifications-container").css("opacity", "0.2");
		} else {
			$(".dashboard-notifications-container").css("background-image", "");
			$(".dashboard-notifications-container").css("opacity", "1.0");
		}
	}
	for(var i = 0; i < notifications.length; i++){
		// Status handling
		var notifMessage = "", notifClass = "", notif_link = "", notif_image = "", notif_icon = "", notif_message = "";
		if(notifications[i].status == '1'){
			notifClass += "notif-new";
		} else {
			notifClass += "notif-old";
		}
		if(i == notifications.length-1){
			notifClass += " waypoint-mark";
		}
		notifMessage += "<li id='notification-"+notifications[i].id+"' data-notification='"+notifications[i].id+"' class='notification-line "+notifClass+" container-fluid'";

		// Token handling
		switch(notifications[i].type){
			case "PRD":
				notif_link = "user/"+notifications[i].user_id+"/abonnements";
				notif_image = notifications[i].photo;
				switch(notifications[i].subtype){
					case "NE":
						notif_message = "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" arrivera à expiration le <strong>"+moment(notifications[i].product_validity).format("DD/MM/YYYY")+"</strong>.";
						break;

					case "NH":
						notif_message = "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" n'a plus que <strong>"+notifications[i].remaining_hours+" heures restantes</strong>.";
						break;

					case "E":
						notif_message = "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" a expiré le <strong>"+moment(notifications[i].product_usage).format("DD/MM/YYYY")+"</strong>.";
						break;
				}
				notif_icon = "glyphicon-credit-card";
				break;

			case "MAT":
				notif_link = "user/"+notifications[i].user_id+"/achats#purchase-"+notifications[i].transaction;
				notif_image = notifications[i].photo;
				switch(notifications[i].subtype){
					case "NE":
						notif_message = "L'échéance de <strong>"+notifications[i].payer+"</strong> pour "+notifications[i].maturity_value+" € de la transaction "+notifications[i].transaction+" arrive à sa date limite, fixée au <strong>"+moment(notifications[i].maturity_date).format("DD/MM/YYYY")+"</strong>.";
						break;

					case "E":
						notif_message = "L'échéance de <strong>"+notifications[i].payer+"</strong> pour "+notifications[i].maturity_value+" € de la transaction "+notifications[i].transaction+" prévue pour le  "+moment(notifications[i].maturity_date).format("DD/MM/YYYY")+" a expiré.";
						break;

					case "L":
						notif_message = "L'échéance de <strong>"+notifications[i].payer+"</strong> pour "+notifications[i].maturity_value+" € de la transaction "+notifications[i].transaction+" prévue pour le  "+moment(notifications[i].maturity_date).format("DD/MM/YYYY")+" <strong>est en retard</strong>.";
						break;
				}
				notif_icon = "glyphicon-repeat";
				break;

			case "MAI":
				notif_link = "user/"+notifications[i].user_id;
				notif_image = notifications[i].photo;
				notif_message = "<strong>"+notifications[i].user+"</strong> n'a pas d'adresse mail enregistrée.";
				notif_icon = "glyphicon-envelope";
				break;

			case "SES":
				if(notifications[i].cours_status == 1){
					notif_link = "passages#ph-session-"+notifications[i].cours_id;
				} else {
					notif_link = "cours/"+notifications[i].cours_id;
				}
				notif_image = notifications[i].photo;
				notif_message = "Le cours de <strong>"+notifications[i].cours_name+"</strong> tenu par "+notifications[i].user+" et commençant à "+moment(notifications[i].cours_start).format("HH:mm")+" en "+notifications[i].salle+" est désormais <strong>ouvert aux participations</strong>.";
				notif_icon = "glyphicon-map-marker";
				break;

			case "TAS":
				notif_link = notifications[i].link;
				notif_image = notifications[i].photo;
				switch(notifications[i].subtype){
					case "A":
						notif_message = "La tâche <strong>"+notifications[i].title+"</strong> vous a été assignée.";
						break;

					case "NE":
						notif_message = "La tâche <strong>"+notifications[i].title+"</strong> arrive bientôt à sa date limite, fixée au <strong>"+moment(notifications[i].deadline).format("ll [à] HH:mm")+"</strong>";
						break;

					case "L":
						notif_message = "La tâche <strong>"+notifications[i].title+"</strong> a dépassé sa date limite du <strong>"+moment(notifications[i].deadline).format("ll [à] HH:mm")+"</strong>";
						break;

					case "CMT":
						notif_message = "Un commentaire a été ajouté à votre tâche <strong>"+notifications[i].title+"</strong>";
						break;
				}
				notif_icon = "glyphicon-list-alt";
				break;

			case "PRO":
				notif_link = "forfait/"+notifications[i].product_id;
				notif_image = "assets/images/sticker_promo.png";
				switch(notifications[i].subtype){
					case "S":
						notif_message = "La promotion du produit <strong>"+notifications[i].product_name+"</strong> commence aujourd'hui et durera jusqu'au "+moment(notifications[i].date_desactivation).format("ll");
						break;

					case "E":
						notif_message = "La promotion du produit <strong>"+notifications[i].product_name+"</strong> s'est achevée aujourd'hui."
						break;
				}
				notif_icon = "glyphicon-euro";
				break;

			default:
				break;
		}

		notifMessage += "data-redirect='"+notif_link+"'>";
		notifMessage += "<div class='notif-pp col-sm-2'>";
		notifMessage += "<img src='"+notif_image+"' alt='Notification'>";
		notifMessage += "</div>";
		notifMessage += "<div class='col-sm-10'>";
		notifMessage += "<div class='row'>";
		notifMessage += "<p class='col-sm-11'>"+notif_message+"</p>";
		if(notifications[i].status == 1){
			notifMessage += "<span class='glyphicon glyphicon-ok-circle col-sm-1 glyphicon-button toggle-read' title='Marquer comme lue'></span>";
		} else {
			notifMessage += "<span class='glyphicon glyphicon-ok-sign col-sm-1 glyphicon-button toggle-read' title='Marquer comme non lue'></span>";
		}
		notifMessage += "<p class='notif-hour col-sm-10'><span class='glyphicon "+notif_icon+"'></span> ";
		notifMessage += ""+moment(notifications[i].date).fromNow()+"</p>";
		notifMessage += "</div>";
		notifMessage += "</div>";
		notifMessage += "</li>";

		$("."+destination).append(notifMessage);
	}
	setTimeout(fetchNotifications, 10000, limit, destination);
}

function badgeNotifications(){
	$.get("functions/badge_notifications.php").done(function(data){
		if(data == 0){
			$(".badge-notifications").hide();
		} else {
			$(".badge-notifications").show();
			$(".badge-notifications").html(data);
		}
		setTimeout(badgeNotifications, 10000);
	})
}
