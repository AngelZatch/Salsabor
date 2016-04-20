moment.locale('fr');
$(document).on('click', '.trigger-nav', function(e){
	e.stopPropagation();
	if($(".sub-modal-notification").is(":visible")){
		$(".sub-modal-notification").hide(0);
	} else {
		$(".smn-body").empty();
		$.when(fetchNotifications(10)).done(function(data){
			displayNotifications(data, 10);
			$(".sub-modal-notification").css({left: 64+"%", top:55+"px"});
			$(".sub-modal-notification").show(0);
		});
	}
}).on('click', '.smn-close', function(e){
	e.stopPropagation();
	$(".sub-modal-notification").hide(0);
})

function fetchNotifications(limit){
	return $.get("functions/fetch_notifications.php", {limit : limit});
}

function displayNotifications(data, limit){
	var notifications = JSON.parse(data);
	for(var i = 0; i < notifications.length; i++){
		// Status handling
		var notifMessage = "", notifClass = "";
		if(notifications[i].status == '1'){
			notifClass = "notif-new";
		} else {
			notifClass = "notif-old";
		}
		notifMessage += "<li class='notification-line "+notifClass+" container-fluid'";

		// Token handling
		switch(notifications[i].type){
			case "PRD":
				notifMessage += "onclick=window.location='user/"+notifications[i].user_id+"/abonnements'>";
				notifMessage += "<div class='notif-pp col-sm-2'><image src='"+notifications[i].photo+"'></div><div class='col-sm-10'>";
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
				notifMessage += "</p><p class='notif-hour'><span class='glyphicon glyphicon-repeat'></span> ";
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
				notifMessage += "</p><p class='notif-hour'><span class='glyphicon glyphicon-mail'></span> ";
				break;


			case "MAI":
				notifMessage += "onclick=window.location='user/"+notifications[i].user_id+"'>";
				notifMessage += "<div class='notif-pp col-sm-2'><image src='"+notifications[i].photo+"'></div><div class='col-sm-10'>";
				notifMessage += "<strong>"+notifications[i].user+"</strong> n'a pas d'adresse mail enregistrée.";
				notifMessage += "</p><p class='notif-hour'><span class='glyphicon glyphicon-envelope'></span> ";
				break;

			default:
				break;
		}
		notifMessage += ""+moment(notifications[i].date).fromNow()+"</div>";
		notifMessage += "</li>";

		if(limit == 0){
			$(".notifications-container").append(notifMessage);
		} else {
			$(".smn-body").append(notifMessage);
		}
	}
}
