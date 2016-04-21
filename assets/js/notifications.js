moment.locale('fr');
$(document).on('click', '.trigger-nav', function(e){
	e.stopPropagation();
	if($(".sub-modal-notification").is(":visible")){
		$(".sub-modal-notification").hide(0);
	} else {
		fetchNotifications(50);
		$(".sub-modal-notification").css({left: 64+"%", top:55+"px"});
		$(".sub-modal-notification").show(0);
	}
}).on('click', '.smn-close', function(e){
	e.stopPropagation();
	$(".sub-modal-notification").hide(0);
}).on('click', '.toggle-read', function(e){
	e.stopImmediatePropagation();
	var notification_id = $(this).parents("li").data().notification;
	var old_value = $(this).parents("li").data().state;
	changeState(notification_id, old_value);
}).on('click', '.notification-line', function(){
	if($(this).data().state == 1){
		var notification_id = $(this).data().notification;
		changeState(notification_id, $(this).data().state);
	}
	window.location = $(this).data().redirect;
})

function fetchNotifications(limit){
	$.get("functions/fetch_notifications.php", {limit : limit}).done(function(data){
		if(limit == 0 || $(".sub-modal-notification").is(":visible")){
			displayNotifications(data, limit);
		}
	});
}

function displayNotifications(data, limit){
	var notifications = JSON.parse(data);
	console.log("displaying");
	if(limit != 0){
		$(".smn-body").empty();
	} else {
		$(".notifications-container").empty();
	}
	for(var i = 0; i < notifications.length; i++){
		// Status handling
		var notifMessage = "", notifClass = "";
		if(notifications[i].status == '1'){
			notifClass = "notif-new";
		} else {
			notifClass = "notif-old";
		}
		notifMessage += "<li id='notification-"+notifications[i].id+"' data-notification='"+notifications[i].id+"' data-state ='"+notifications[i].status+"' class='notification-line "+notifClass+" container-fluid'";

		// Token handling
		switch(notifications[i].type){
			case "PRD":
				notifMessage += "data-redirect='user/"+notifications[i].user_id+"/abonnements'>";
				notifMessage += "<div class='notif-pp col-sm-2'><image src='"+notifications[i].photo+"'></div><div class='col-sm-10'>";
				switch(notifications[i].subtype){
					case "NE":
						notifMessage += "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" arrivera à expiration le <strong>"+moment(notifications[i].product_validity).format("DD/MM/YYYY")+"</strong>.";
						break;

					case "NH":
						notifMessage += "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" n'a plus que <strong>"+notifications[i].remaining_hours+" heures restantes</strong>.";
						break;

					case "E":
						notifMessage += "Le produit <strong>"+notifications[i].product_name+"</strong> de "+notifications[i].user+" a expiré le <strong>"+moment(notifications[i].product_usage).format("DD/MM/YYYY")+"</strong>.";
						break;
				}
				notifMessage += "</p><p class='notif-hour col-sm-10'><span class='glyphicon glyphicon-credit-card'></span> ";
				break;

			case "MAT":
				notifMessage += "data-redirect='user/"+notifications[i].user_id+"/achats#purchase-"+notifications[i].transaction+"'>";
				notifMessage += "<div class='notif-pp col-sm-2'><image src='"+notifications[i].photo+"'></div><div class='col-sm-10'>";
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
				notifMessage += "</p><p class='notif-hour col-sm-10'><span class='glyphicon glyphicon-repeat'></span> ";
				break;

			case "TRA":
				notifMessage += ">";
				notifMessage += "<div class='notif-pp col-sm-2'><image src='"+notifications[i].photo+"'></div><div class='col-sm-10'>";
				switch(notifications[i].subtype){
					case "NE":
						break;

					case "E":
						break;

					case "L":
						break;
				}
				notifMessage += "</p><p class='notif-hour col-sm-10'><span class='glyphicon glyphicon-mail'></span> ";
				break;


			case "MAI":
				notifMessage += "data-redirect='user/"+notifications[i].user_id+"'>";
				notifMessage += "<div class='notif-pp col-sm-2'><image src='"+notifications[i].photo+"'></div><div class='col-sm-10'>";
				notifMessage += "<strong>"+notifications[i].user+"</strong> n'a pas d'adresse mail enregistrée.";
				notifMessage += "</p><p class='notif-hour col-sm-10'><span class='glyphicon glyphicon-envelope'></span> ";
				break;

			case "SES":
				notifMessage += "data-redirect='passages#ph-session-"+notifications[i].id+"'>";
				notifMessage += "<div class='notif-pp col-sm-2'><image src='"+notifications[i].photo+"'></div><div class='col-sm-10'>";
				notifMessage += "Le cours de <strong>"+notifications[i].cours_name+"</strong> tenu par "+notifications[i].user+" et commençant à "+moment(notifications[i].cours_start).format("HH:mm")+" en "+notifications[i].salle+" est désormais <strong>ouvert aux passages</strong>.";
				notifMessage += "</p><p class='notif-hour col-sm-10'><span class='glyphicon glyphicon-map-marker'></span> ";

			default:
				break;
		}
		notifMessage += ""+moment(notifications[i].date).fromNow()+"</p>";
		if(notifications[i].status == 1){
			notifMessage += "<span class='glyphicon glyphicon-ok-circle col-sm-1 glyphicon-button toggle-read' title='Marquer comme lue'></span>";
		} else {
			notifMessage += "<span class='glyphicon glyphicon-ok-sign col-sm-1 glyphicon-button toggle-read' title='Marquer comme non lue'></span>";
		}
		notifMessage += "</div>";
		notifMessage += "</li>";

		if(limit == 0){
			$(".notifications-container").append(notifMessage);
		} else {
			$(".smn-body").append(notifMessage);
		}
	}
	setTimeout(fetchNotifications, 10000, limit);
}

function changeState(notification_id, old_value){
	$.when(toggleBoolean(null, "notification_state", notification_id, "notification_id", old_value)).done(function(){
		if(old_value == 0){
			$("#notification-"+notification_id).removeClass("notif-old");
			$("#notification-"+notification_id).addClass("notif-new");
			var span = $("#notification-"+notification_id).find("span.glyphicon-button");
			span.replaceWith("<span class='glyphicon glyphicon-ok-circle col-sm-1 glyphicon-button toggle-read' title='Marquer comme lue'></span>");
			$("#notification-"+notification_id).data().state = 1;
			$("#badge-notifications").html(parseInt($("#badge-notifications").html())+1);
		} else {
			$("#notification-"+notification_id).removeClass("notif-new");
			$("#notification-"+notification_id).addClass("notif-old");
			var span = $("#notification-"+notification_id).find("span.glyphicon-button");
			span.replaceWith("<span class='glyphicon glyphicon-ok-sign col-sm-1 glyphicon-button toggle-read' title='Marquer comme non lue'></span>");
			$("#badge-notifications").html(parseInt($("#badge-notifications").html())-1);
		}
	})
}

function badgeNotifications(){
	$.get("functions/badge_notifications.php").done(function(data){
		if(data == 0){
			$("#badge-notifications").hide();
		} else {
			$("#badge-notifications").show();
			$("#badge-notifications").html(data);
		}
		setTimeout(badgeNotifications, 10000);
	})
}
