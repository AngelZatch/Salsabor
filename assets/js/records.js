$(document).ready(function(){
	// Init by display all the active sessions
	/* The goal here is to fetch all the active sessions when the page is loaded, then to wait 15 minutes before going to see if new sessions were activated. Thus, every 15 minutes we have to only get the newly activated sessions, which means the sessions that will begin in less than 90 minutes away from the time we're checking. As the sessions could have been added in a deorganised manner, we will construct an array of currently displayed sessions by ID to cross check what can be ignored by subsequent fetches.

	The same goes for the participations. We have to fetch the participations of only the sessions that are not collapsed. To do that, we create an array that will contain the non collapsed sessions, and every so often we'll refresh everything at once.
	*/
	var fetched = [];
	window.openedSessions = [];
	moment.locale('fr');
	$.when(displaySessions(fetched)).done(function(){
		refreshTick();
	});
}).on('click', '.panel-heading-container', function(){
	var id = document.getElementById($(this).attr("id")).dataset.session;
	fetchRecords(id);
}).on('shown.bs.collapse', ".panel-body", function(){
	var session_id = document.getElementById($(this).attr("id")).dataset.session;
	displayRecords(session_id);
}).on('hidden.bs.collapse', ".panel-body", function(){
	var session_id = document.getElementById($(this).attr("id")).dataset.session;
}).on('click', '.report-product-record', function(){
	var record_target = document.getElementById($(this).attr("id")).dataset.record;
	if($(this).attr("id") == "btn-product-null-record"){
		var product_target = "-1";
	} else {
		var product_target = document.getElementById("product-selected").dataset.argument;
	}
	changeProductRecord(record_target, product_target);
}).on('click', '.delete-record', function(){
	var record_id = document.getElementById($(this).attr("id")).dataset.record;
	console.log(record_id);
	deleteRecord(record_id);
}).on('click', function(e){
	if($(".sub-modal:hidden")){
		$(".sub-modal").hide();
	}
}).on('focus', '.name-input', function(){
	$.post("functions/get_user_list.php").done(function(data){
		var userList = JSON.parse(data);
		var autocompleteList = [];
		for(var i = 0; i < userList.length; i++){
			autocompleteList.push(userList[i].user);
		}
		$(".name-input").textcomplete([{
			match: /(^|\b)(\w{2,})$/,
			search: function(term, callback){
				callback($.map(autocompleteList, function(item){
					return item.indexOf(term) === 0 ? item : null;
				}));
			},
			replace: function(item){
				return item;
			}
		}]);
	});
	/*$(this).keypress(function(event){
		if(event.which == 13){
			console.log("coucou");
		}
	})*/
}).on('click', '.add-record', function(){
	var name = $(".name-input").val();
	var session_id = document.getElementById($(this).attr("id")).dataset.session;
	addRecord(session_id, name);
}).on('click', '.validate-session', function(e){
	e.stopPropagation();
	var session_id = document.getElementById($(this).attr("id")).dataset.session;
	var record_ids = $("#body-session-"+session_id).find("li:not(.panel-add-record)").each(function(){
		if($(this).hasClass("status-pre-success") || $(this).hasClass("status-over")){
			validateRecord(document.getElementById($(this).attr("id")).dataset.record);
		}
	});
}).on('click', '.close-session', function(e){
	e.stopPropagation();
	var session_id = document.getElementById($(this).attr("id")).dataset.session;
	closeSession(session_id);
})

function displaySessions(fetched){
	$.get("functions/fetch_active_sessions.php", {fetched : fetched}).done(function(data){
		var active_sessions = JSON.parse(data);
		var as_display = "";
		$(".active-sessions-container").append(as_display);
		for(var i = 0; i < active_sessions.length; i++){
			var cours_start = moment(active_sessions[i].start);
			/*if(cours_start > moment().format("DD/MM/YYYY HH:mm")){
				var relative_time = cours_start.toNow();
			} else {
				var relative_time = cours_start.fromNow();
			}*/
			as_display += "<div class='panel panel-session' id='session-"+active_sessions[i].id+"'>";
			// Panel heading
			as_display += "<a class='panel-heading-container' id='ph-session-"+active_sessions[i].id+"' data-session='"+active_sessions[i].id+"'>";
			as_display += "<div class='panel-heading'>";
			// Container fluid for session name and hour
			as_display += "<div class='container-fluid'>";
			as_display += "<p class='session-id col-lg-5'>"+active_sessions[i].title+"</p>";
			as_display += "<p class='session-date col-lg-5'><span class='glyphicon glyphicon-time'></span> Le "+cours_start.format("DD/MM")+" de "+cours_start.format("HH:mm")+" à "+moment(active_sessions[i].end).format("HH:mm")+"</p>";
			as_display += "<p class='col-lg-1 session-option'><span class='glyphicon glyphicon-lock close-session' id='close-session-"+active_sessions[i].id+"' data-session='"+active_sessions[i].id+"' title='Verrouiller le cours'></span></p>";
			as_display += "<p class='col-lg-1 session-option'><span class='glyphicon glyphicon-ok-sign validate-session' id='validate-session-"+active_sessions[i].id+"' data-session='"+active_sessions[i].id+"' title='Valider tous les passages'></span></p>";
			as_display += "</div>";
			// Container fluid for session level, teacher...
			as_display += "<div class='container-fluid'>";
			as_display += "<p class='col-lg-1'><span class='glyphicon glyphicon-user'></span> <span class='user-total-count' id='user-total-count-"+active_sessions[i].id+"'></span></p>";
			as_display += "<p class='col-lg-1'><span class='glyphicon glyphicon-ok'></span> <span class='user-ok-count' id='user-ok-count-"+active_sessions[i].id+"'></span></p>";
			as_display += "<p class='col-lg-1'><span class='glyphicon glyphicon-warning-sign'></span> <span class='user-warning-count' id='user-warning-count-"+active_sessions[i].id+"'></span></p>";
			as_display += "<p class='col-lg-3'><span class='glyphicon glyphicon-signal'></span> "+active_sessions[i].level+"</p>";
			as_display += "<p class='col-lg-3'><span class='glyphicon glyphicon-pushpin'></span> "+active_sessions[i].room+"</p>";
			as_display += "<p class='col-lg-3'><span class='glyphicon glyphicon-blackboard'></span> "+active_sessions[i].teacher+"</p>";
			as_display += "</div>";

			as_display += "</div>";
			as_display += "</a>";
			// Panel body
			as_display += "<div class='panel-body collapse' id='body-session-"+active_sessions[i].id+"' data-session='"+active_sessions[i].id+"'>";
			as_display += "</div></div>";
			fetched.push(active_sessions[i].id);
			window.openedSessions.push(parseInt(active_sessions[i].id));
		}
		$(".active-sessions-container").append(as_display);
		var opened = $(".panel-session").length;
		switch(opened){
			case 0:
				$(".sub-legend").html("<span></span> Aucun cours n'est ouvert");
				break;

			case 1:
				$(".sub-legend").html("<span></span> cours est actuellement ouvert");
				$(".sub-legend>span").html(opened);
				break;

			default:
				$(".sub-legend").html("<span></span> cours sont actuellements ouverts");
				$(".sub-legend>span").html(opened);
				break;
		}
		/*console.log(fetched);*/
		/*setTimeout(displaySessions, 5000, fetched);*/
		setTimeout(displaySessions, 60000, fetched);
	})
}

function fetchRecords(session_id){
	$("#body-session-"+session_id).collapse("toggle");
}

/** To have up-to-date info on every non collapsed session, this function ensures the info is refreshed every so often. Of course, when something big such as a deletion is done, displayRecords can be called independently as it won't affect the global tick. **/
function refreshTick(){
	var openedSessions = window.openedSessions;
	console.log(openedSessions);
	for(var i = 0; i < openedSessions.length; i++){
		displayRecords(openedSessions[i]);
	}
	// The tick is set to every 10 seconds.
	setTimeout(refreshTick, 10000);
}

function displayRecords(session_id){
	$.get("functions/fetch_records_session.php", {session_id : session_id}).done(function(data){
		console.log("showing"+session_id);
		var records_list = JSON.parse(data);
		$("#body-session-"+session_id).empty();
		var contents = "<div class='row session-list-container' id='session-"+session_id+">";
		contents += "<ul class='records-inside-list records-product-list'>";
		var users = 0, ok = 0, warning = 0;
		for(var i = 0; i <= records_list.length; i++){
			if(i == records_list.length){
				contents += "<li class='panel-item panel-record panel-add-record container-fluid trigger-sub col-lg-3' id='add-record-"+session_id+"' data-subtype='add-record' data-session='"+session_id+"'>";
				contents += "<div class='small-user-pp empty-pp'></div>";
				contents += "<p class='col-lg-12 panel-item-title bf'>Ajouter un passage manuellement</p>";
				contents += "</li>";
			} else {
				var record_status;
				switch(records_list[i].status){
					case '0':
						record_status = "status-pre-success";
						break;

					case '2':
						if(records_list[i].product_name == "-"){
							record_status = "status-partial-success";
						} else {
							record_status = "status-success";
						}
						ok++;
						break;

					case '3':
						record_status = "status-over";
						warning++;
						break;
				}
				users++;
				contents += "<li class='panel-item panel-record "+record_status+" container-fluid col-lg-3' id='session-record-"+records_list[i].id+"' data-record='"+records_list[i].id+"'>";
				contents += "<div class='small-user-pp'><img src='"+records_list[i].photo+"'></div>";
				contents += "<p class='col-lg-12 panel-item-title bf'>"+records_list[i].user+"</p>";
				contents += "<p class='col-lg-6 session-record-details'><span class='glyphicon glyphicon-time'></span> "+moment(records_list[i].date).format("HH:mm:ss")+"</p>";
				contents += "<p class='col-lg-6 session-record-details'><span class='glyphicon glyphicon-qrcode'></span> "+records_list[i].card+"</p>";
				// Indicating the product will soon expire
				if(moment(records_list[i].product_expiration).isBefore(moment('now').add(5, 'days'))){
					contents += "<p class='col-lg-12 session-record-details srd-product product-soon' title='Expiration prochaine : "+moment(records_list[i].product_expiration).format("DD/MM/YYYY")+"'><span class='glyphicon glyphicon-credit-card'></span> "+records_list[i].product_name+"</p>";
				} else if(records_list[i].product_hours <= 3){
					contents += "<p class='col-lg-12 session-record-details srd-product product-soon' title='Expiration prochaine : "+records_list[i].product_hours+" heures restantes'><span class='glyphicon glyphicon-credit-card'></span> "+records_list[i].product_name+"</p>";
				} else {
					contents += "<p class='col-lg-12 session-record-details srd-product'><span class='glyphicon glyphicon-credit-card'></span> "+records_list[i].product_name+"</p>";
				}
				// Different button depending on the status of the record
				if(records_list[i].status == '2'){
					contents += "<p class='col-lg-3 panel-item-options' id='option-validate'><span class='glyphicon glyphicon-remove glyphicon-button' onclick='unvalidateRecord("+records_list[i].id+")' title='Annuler la validation'></span></p>";
				} else {
					contents += "<p class='col-lg-3 panel-item-options' id='option-validate'><span class='glyphicon glyphicon-ok glyphicon-button' onclick='validateRecord("+records_list[i].id+")' title='Valider le passage'></span></p>";
				}
				contents += "<p class='col-lg-3 panel-item-options'><span class='glyphicon glyphicon-arrow-right glyphicon-button trigger-sub' id='change-product-"+records_list[i].id+"' data-subtype='report-record' data-argument='"+records_list[i].id+"' title='Changer le produit'></span></p>";
				contents += "<p class='col-lg-3 panel-item-options'><span class='glyphicon glyphicon-pushpin glyphicon-button trigger-sub' id='change-session-"+records_list[i].id+"' data-subtype='change-session-record' data-argument='"+records_list[i].id+"' title='Changer le cours'></span></p>";
				contents += "<p class='col-lg-3 panel-item-options'><span class='glyphicon glyphicon-trash glyphicon-button trigger-sub' id='delete-record-"+records_list[i].id+"' data-subtype='delete-record' data-argument='"+records_list[i].id+"' title='Supprimer le passage'></span></p>";
				contents += "</li>";
			}
		}
		contents += "</ul>";
		contents += "</div>";
		$("#body-session-"+session_id).append(contents);
		$("#user-total-count-"+session_id).text(users);
		$("#user-ok-count-"+session_id).text(ok);
		$("#user-warning-count-"+session_id).text(warning);
	})
}

function validateRecord(record_id){
	$.post("functions/validate_record.php", {record_id : record_id}).done(function(product_id){
		$("#session-record-"+record_id).removeClass("status-pre-success");
		$("#session-record-"+record_id).removeClass("status-over");
		if(product_id == ""){
			$("#session-record-"+record_id).addClass("status-partial-success");
		} else {
			$("#session-record-"+record_id).addClass("status-success");
			computeRemainingHours(product_id, false);
		}
		$("#session-record-"+record_id+">#option-validate").html("<span class='glyphicon glyphicon-remove glyphicon-button' onclick='unvalidateRecord("+record_id+")' title='Annuler la validation'></span>")
	})
}

function unvalidateRecord(record_id){
	$.post("functions/unvalidate_record.php", {record_id : record_id}).done(function(result){
		var data = JSON.parse(result);
		console.log(data);
		var status = data.status, product_id = data.product_id;
		$("#session-record-"+record_id).removeClass("status-success");
		$("#session-record-"+record_id).removeClass("status-partial-success");
		if(status == 0){
			$("#session-record-"+record_id).addClass("status-pre-success");
			computeRemainingHours(product_id, false);
		} else {
			$("#session-record-"+record_id).addClass("status-over");
		}
		$("#session-record-"+record_id+">#option-validate").html("<span class='glyphicon glyphicon-ok glyphicon-button' onclick='validateRecord("+record_id+")' title='Valider le passage'></span>");
	})
}

function deleteRecord(record_id){
	if($("#session-record-"+record_id).hasClass("status-success")){
		unvalidateRecord(record_id);
	}
	$.post("functions/delete_record.php", {record_id}).done(function(){
		$("#session-record-"+record_id).remove();
	})
}

/** This function will change the product the record will use when it's validated. If the record was valid before, then it'll be unvalidated to allow computing of the previous product, switched and then validated again for computing. **/
function changeProductRecord(record_id, target_product_id){
	if(target_product_id == null){
		console.log("No product has been indicated. Aborting...");
	} else {
		var wasValid = false;
		if($("#session-record-"+record_id).hasClass("status-success")){
			$.when(unvalidateRecord(record_id)).done(function(){
				console.log("Unvalidate record "+record_id);
				wasValid = true;
			});
		}
		$.post("functions/set_product_record.php", {record_id : record_id, product_id : target_product_id}).done(function(response){
			var data = JSON.parse(response);
			var product_name = data.product_name, status = data.status;
			console.log(status);
			$("#session-record-"+record_id+">p.srd-product").html("<span class='glyphicon glyphicon-credit-card'></span> "+product_name);
			$(".sub-modal").hide();
			if(wasValid){
				validateRecord(record_id);
			} else {
				$("#session-record-"+record_id).removeClass("status-pre-success");
				$("#session-record-"+record_id).removeClass("status-over");
				if(status == '0'){
					$("#session-record-"+record_id).addClass("status-pre-success");
				} else {
					$("#session-record-"+record_id).addClass("status-over");
				}
			}
		})
	}
}

/** Similarly to the function above, this one will also fiddle with the validation if need be. Its main goal is changing the session attached to a record if a user just validated in the wrong place. It happens. More often that not. **/
function changeSessionRecord(record_id, target_session_id){
	if(target_session_id == null){
		console.log("No session has been indicated. Aborting...");
	} else {
		var wasValid = false;
		if($("#session-record-"+record_id).hasClass("status-success")){
			$.when(unvalidateRecord(record_id)).done(function(){
				wasValid = true;
			})
		}
		$.post("functions/set_session_record.php", {record_id : record_id, session_id : target_session_id}).done(function(){
			$("#session-record-"+record_id).remove();
			if(wasValid){
				validateRecord(record_id);
			}
			displayRecords(target_session_id);
		})
	}

}

function addRecord(target_session_id, user_name){
	$.post("functions/add_record.php", {name : user_name, session_id : target_session_id}).done(function(){
		console.log("Record added");
		displayRecords(target_session_id);
	})
}

/** Close a session will make it disappear from the records page by changing its state to 0.
(0 : closed, 1 : opened and available for automatic records, 2 : opened but closed to automatic records)**/
function closeSession(session_id){
	$.post("functions/close_session.php", {session_id : session_id}).done(function(){
		$("#session-"+session_id).remove();
		// We remove the recently closed session from the list to be refreshed.
		switch(window.openedSessions.length){
			case 0:
				break;

			case 1: // jQuery.grep() cannot empty an array
				window.openedSessions.length = 0;
				break;

			default:
				window.openedSessions = jQuery.grep(window.openedSessions, function(arr){
					return arr !== parseInt(session_id);
				})
		}
	})
}
