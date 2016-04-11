$(document).ready(function(){
	moment.locale('fr');
	$("#product-modal").on('show.bs.modal', function(event){
		var argument = $(event.relatedTarget).data('argument'), modal = $(this);

		$.when(fetchProduct(argument), fetchSessions(argument)).done(function(product, sessions){
			var product_details = JSON.parse(product[0]), buttons = "";

			// Handling the product
			modal.find(".modal-title").text(product_details.product+" (ID : "+product_details.id+")");
			modal.find(".purchase-sub").text("Transaction "+product_details.transaction+" du "+moment(product_details.transaction_date).format("DD/MM/YYYY")+"; utilisé par "+product_details.user);

			switch(product_details.status){
				case '0':
					/*buttons += "<button class='btn btn-default btn-block btn-modal' id='btn-activate-"+product_details.id+"' onclick='activateProduct("+product_details.id+")'><span class='glyphicon glyphicon-play-circle'></span> Activer</button>";*/
					// Activation button
					buttons += "<button class='btn btn-default btn-block btn-modal trigger-sub' id='btn-activate-"+product_details.id+"' data-argument='"+product_details.id+"' data-subtype='activate'><span class='glyphicon glyphicon-play-circle'></span> Activer</button>";
					break;

				case '1':
					// Deactivation button
					buttons += "<button class='btn btn-default btn-block btn-modal' id='btn-activate-"+product_details.id+"' onclick='deactivateProduct("+product_details.id+")'><span class='glyphicon glyphicon-ban-circle'></span> Désactiver</button>";
					// Extension button
					buttons += "<button class='btn btn-default btn-block btn-modal trigger-sub' id='btn-arep' data-argument='"+product_details.id+"' data-arep='"+product_details.extended+"' data-subtype='AREP'><span class='glyphicon glyphicon-calendar'></span> AREP</button>";
					break;

				case '2':
					// Reactivation button
					buttons += "<button class='btn btn-default btn-block btn-modal trigger-sub' id='btn-activate-"+product_details.id+"' data-argument='"+product_details.id+"' data-subtype='activate'><span class='glyphicon glyphicon-play-circle'></span> Réactiver</button>";
					// Extension button
					buttons += "<button class='btn btn-default btn-block btn-modal trigger-sub' id='btn-arep' data-argument='"+product_details.id+"' data-arep='"+product_details.extended+"' data-subtype='AREP'><span class='glyphicon glyphicon-calendar'></span> AREP</button>";
					break;
			}
			if(product_details.flag_hours == '1'){ // If the product is not an illimited, a private lesson or an annual subscription
				if(product_details.remaining_hours < 0){
					var product_validity = "<p id='product-status-"+product_details.id+"'><span class='highlighted-value'>"+-1 * product_details.remaining_hours+" heures</span> de consommation excessive</p>";
				} else {
					var product_validity = "<p id='product-status-"+product_details.id+"'><span class='highlighted-value'>"+product_details.remaining_hours+" heures</span><br>restantes</p>";
				}
			} else {
				if(product_details.status == '1'){ // If the product is active
					var product_validity = "<p id='product-status"+product_details.id+"'><span class='highlighted-value'>"+moment(product_details.validity).toNow(true)+"</span><br> restants</p>";
				}
			}
			if(product_details.subscription == 0){ // If the product is NOT an annual subscription
				// Computing hours button
				buttons += "<button class='btn btn-default btn-block btn-modal' onclick='computeRemainingHours("+product_details.id+", true)'><span class='glyphicon glyphicon-scale'></span> Recalculer</button>";
				buttons += "<button class='btn btn-default btn-block btn-modal' onclick='unlinkAll()' title='Délier tous les cours hors forfait'><span class='glyphicon glyphicon-link'></span> Délier inval.</button>";
				buttons += "<button class='btn btn-default btn-block btn-modal' id='link-all' onclick='linkAll()' title='Délier tous les cours hors forfait'><span class='glyphicon glyphicon-arrow-right'></span> Trouver assoc.</button>";
				// Handling the sessions
				computeRemainingHours(argument, true);
			} else {
				$(".participations-list").empty();
			}
			if(product_details.auto_status == 1){
				var expiredAffix = "disabled";
			} else {
				var expiredAffix = "enabled";
			}
			buttons += "<button class='btn btn-default btn-block btn-modal "+expiredAffix+"' id='manual-expire' onclick='deactivateProduct("+product_details.id+", 2)'><span class='glyphicon glyphicon-hourglass'></span> Expirer</button>";
			buttons += "<button class='btn btn-danger btn-block btn-modal trigger-sub' id='delete-product' data-subtype='delete-product' data-product='"+product_details.id+"'><span class='glyphicon glyphicon-trash'></span> Supprimer</button>";
			buttons += "<h2 class='modal-body-title'>Calculs auto.</h2>";
			// Button to toggle automatic computing of this product.
			if(product_details.auto_status == 1){
				buttons += "<button class='btn btn-default btn-block btn-modal btn-boolean status-enabled' id='auto_status' data-product='"+product_details.id+"' data-boolean='"+product_details.auto_status+"'><span class='glyphicon glyphicon-floppy-saved'></span> Etat</button>";
			} else {
				buttons += "<button class='btn btn-default btn-block btn-modal btn-boolean status-disabled' id='auto_status' data-product='"+product_details.id+"' data-boolean='"+product_details.auto_status+"'><span class='glyphicon glyphicon-floppy-remove'></span> Etat</button>";
			}
			if(product_details.auto_dates == 1){
				buttons += "<button class='btn btn-default btn-block btn-modal btn-boolean status-enabled' id='auto_dates' data-product='"+product_details.id+"' data-boolean='"+product_details.auto_dates+"'><span class='glyphicon glyphicon-floppy-saved'></span> Dates</button>";
			} else {
				buttons += "<button class='btn btn-default btn-block btn-modal btn-boolean status-disabled' id='auto_dates' data-product='"+product_details.id+"' data-boolean='"+product_details.auto_dates+"'><span class='glyphicon glyphicon-floppy-remove'></span> Dates</button>";
			}
			modal.find(".product-validity").empty();
			modal.find(".product-validity").html(product_validity);
			modal.find(".modal-actions").html(buttons);
		})
	}).on('hidden.bs.modal', function(event){
		$(".sub-modal").hide();
	})
}).on('click', '.trigger-sub', function(e){
	e.stopPropagation();
	$(".sub-modal").hide(0);
	$(".sub-modal-body").empty();
	var target = document.getElementById($(this).attr("id"));
	var tpos = $(this).position(), type = target.dataset.subtype, toffset = $(this).offset();
	/*console.log(product_id, type);*/

	var title, body = "", footer = "";
	switch(type){
		case 'AREP':
			var product_id = target.dataset.argument;
			title = "Prolonger";
			body += "<input type='text' class='form-control datepicker'/>";
			footer += "<button class='btn btn-success extend-product' data-argument='"+product_id+"' id='btn-sm-extend'>Prolonger</button>";
			if(moment(target.dataset.arep).isValid()){
				footer += "<button class='btn btn-danger remove-extension' data-argument='"+product_id+"' id='btn-sm-unextend'>Annuler AREP</button>";
			}
			$(".sub-modal").css({top : tpos.top+51+'px'});
			$(".sub-modal-body").html(body);
			break;

		case 'activate':
			var product_id = target.dataset.argument;
			title = "Activer";
			body += "<input type='text' class='form-control datepicker'/>";
			footer += "<button class='btn btn-success activate-product' data-argument='"+product_id+"' id='btn-sm-activate'>Activer</button>";
			$(".sub-modal").css({top : tpos.top+51+'px'});
			$(".sub-modal-body").html(body);
			break;

		case 'report':
			title = "Assigner à un autre produit";
			var participation_id = target.dataset.argument;
			//displayEligibleProducts(record_id);
			$.when(fetchEligibleProducts(participation_id, "participation")).done(function(data){
				var construct = displayEligibleProducts(data);
				$(".sub-modal-body").html(construct);
			})
			footer += "<button class='btn btn-success report-product' id='btn-product-report' data-session='"+participation_id+"'>Reporter</button>";
			$(".sub-modal").css({top : tpos.top-45+'px'});
			break;

		case 'report-record':
			title = "Changer le produit à utiliser";
			var record_id = target.dataset.argument;
			$.when(fetchEligibleProducts(record_id, "record")).done(function(data){
				var construct = displayEligibleProducts(data);
				$(".sub-modal-body").html(construct);
			})
			footer += "<button class='btn btn-success report-product-record' id='btn-product-report-record' data-record='"+record_id+"'>Reporter</button>";
			footer += " <button class='btn btn-default btn-modal report-product-record' id='btn-product-null-record' data-record='"+record_id+"'><span class='glyphicon glyphicon-link'></span> Retirer</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			if(toffset.left > 1000){
				$(".sub-modal").css({left : toffset.left-350+'px'});
			} else {
				$(".sub-modal").css({left : toffset.left+20+'px'});
			}
			break;

		case 'change-session-record':
			title = "Changer le lieu du passage";
			$.when(fetchActiveSessions()).done(function(data){
				console.log(data);
				var construct = displayTargetSessions(data);
				$(".sub-modal-body").html(construct);
			})
			var record_id = target.dataset.argument;
			footer += "<button class='btn btn-success report-session-record' id='btn-session-changer-record' data-record='"+record_id+"'>Changer</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			if(toffset.left > 1000){
				$(".sub-modal").css({left : toffset.left-350+'px'});
			} else {
				$(".sub-modal").css({left : toffset.left+20+'px'});
			}
			break;

		case 'delete':
			title = "Supprimer une participation";
			var participation_id = target.dataset.argument;
			body += "Êtes-vous sûr de vouloir supprimer cette participation ?";
			$(".sub-modal-body").html(body);
			footer += "<button class='btn btn-danger delete-participation col-lg-6' id='btn-product-delete' data-session='"+participation_id+"'><span class='glyphicon glyphicon-trash'></span> Supprimer</button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : tpos.top-45+'px'});
			break;

		case 'delete-record':
			title = "Supprimer un passage";
			var record_id = target.dataset.argument;
			body += "Êtes-vous sûr de vouloir supprimer ce passage ?";
			$(".sub-modal-body").html(body);
			footer += "<button class='btn btn-danger delete-record col-lg-6' id='btn-record-delete' data-record='"+record_id+"'><span class='glyphicon glyphicon-trash'></span> Supprimer</button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			if(toffset.left > 1000){
				$(".sub-modal").css({left : toffset.left-350+'px'});
			} else {
				$(".sub-modal").css({left : toffset.left+20+'px'});
			}
			break;

		case 'delete-product':
			title = "Supprimer un produit";
			var product_id = target.dataset.product;
			body += "ATTENTION : Si ce produit est seul dans une transaction, la transaction sera supprimée avec ce produit. Une fois validée, cette opération destructrice est irréversible. Êtes-vous sûr de vouloir supprimer ce produit ?";
			footer += "<button class='btn btn-danger delete-product col-lg-6' id='btn-product-delete' data-product='"+product_id+"' data-dismiss='modal'><span class='glyphicon glyphicon-trash'></span> Supprimer</button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : tpos.top+51+'px'});
			$(".sub-modal-body").html(body);
			break;

		case 'add-record':
			title = "Ajouter un passage manuellement";
			var session_id = target.dataset.session;
			body += "<input type='text' class='form-control name-input'>";
			$(".sub-modal-body").html(body);
			footer += "<button class='btn btn-success add-record col-lg-6' id='btn-add-record' data-session='"+session_id+"'><span class='glyphicon glyphicon-plus'></span> Ajouter </button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			if(toffset.left > 1000){
				$(".sub-modal").css({left : toffset.left-350+'px'});
			} else {
				$(".sub-modal").css({left : toffset.left+20+'px'});
			}
			break;

		case 'unlink':
			title = "Délier une participation";
			var participation_id = target.dataset.argument;
			body += "Êtes vous sûr de vouloir délier cette participation ? Vous la retrouverez dans les passages non régularisés";
			$(".sub-modal-body").html(body);
			footer += "<button class='btn btn-default unlink-session col-lg-6' id='btn-product-unlink' data-session='"+participation_id+"'><span class='glyphicon glyphicon-link'></span> Délier</button> <button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : tpos.top-45+'px'});
			break;

		default:
			title = "Sub modal";
			break;
	}
	$(".sub-modal-title").text(title);
	$(".sub-modal-footer").html(footer);
	$(".datepicker").datetimepicker({
		format: "DD/MM/YYYY",
		inline: true,
		locale: "fr"
	})
	var re = /historique/i;
	if(re.exec(top.location.pathname) != null){
		console.log("Historique");
		$(".sub-modal").css({left: 74+'%'});
	}
	$(".sub-modal").show(0);
}).on('click', '.activate-product', function(){
	var date = moment($(".datepicker").val(),"DD/MM/YYYY").format("YYYY-MM-DD");
	var product_id = document.getElementById($(this).attr("id")).dataset.argument;
	activateProductWithDate(product_id, date);
}).on('click', '.extend-product', function(){
	var date = moment($(".datepicker").val(),"DD/MM/YYYY").format("YYYY-MM-DD 23:59:59");
	var product_id = document.getElementById($(this).attr("id")).dataset.argument;
	extendProduct(product_id, date);
}).on('click', '.remove-extension', function(){
	var product_id = document.getElementById($(this).attr("id")).dataset.argument;
	extendProduct(product_id, null);
}).on('click', '.product-participation', function(){
	var session = $(this);
	var product_id = document.getElementById($(this).attr("id")).dataset.argument;
	if(!$(this).hasClass("options-shown")){
		session.addClass("options-shown");
		var content = "<div class='session-options'><button class='btn btn-default btn-modal trigger-sub' data-argument='"+product_id+"' data-subtype='report' id='btn-session-report'><span class='glyphicon glyphicon-arrow-right'></span> Réaffecter</button> ";
		content += "<button class='btn btn-default btn-modal trigger-sub' data-argument='"+product_id+"' data-subtype='unlink' id='btn-session-unlink'><span class='glyphicon glyphicon-link'></span> Délier</button> ";
		content += "<button class='btn btn-danger btn-modal trigger-sub' data-argument='"+product_id+"' data-subtype='delete' id='btn-session-delete'><span class='glyphicon glyphicon-trash'></span> Supprimer</button></div>";
		session.append(content);
	} else {
		$(this).find(".session-options").remove();
		session.removeClass("options-shown");
	}
}).on('click', '.sub-modal-product', function(e){
	e.stopPropagation();
	$(".sub-modal-product>span").remove();
	$(".sub-modal-product").attr("id", "");
	$(this).append("<span class='glyphicon glyphicon-ok'></span>");
	$(this).attr("id", "product-selected");
}).on('click', '.report-product', function(){
	var session_target = document.getElementById($(this).attr("id")).dataset.session;
	var product_target = document.getElementById("product-selected").dataset.argument;
	reportSession(product_target, session_target);
}).on('click', '.delete-participation', function(){
	var participation_id = document.getElementById($(this).attr("id")).dataset.session;
	deleteParticipation(participation_id);
}).on('click', '.unlink-session', function(){
	var session_target = document.getElementById($(this).attr("id")).dataset.session;
	unlinkParticipation(session_target);
}).on('click', '.form-control', function(e){
	e.stopPropagation();
}).on('click', '.btn-boolean', function(e){
	e.stopPropagation();
	var button = $(this);
	var boolean_name = $(this).attr("id");
	var product_id = document.getElementById($(this).attr("id")).dataset.product;
	var old_value = document.getElementById($(this).attr("id")).dataset.boolean;
	toggleBoolean(button, boolean_name, product_id, old_value);
}).on('click', '.delete-product', function(){
	var product_id = document.getElementById($(this).attr("id")).dataset.product;
	deleteProduct(product_id);
})

function activateProductWithDate(product_id, start_date){
	$.post("functions/activate_product.php", {product_id : product_id, start_date : start_date}).done(function(data){
		var dates = JSON.parse(data);
		var activation = dates[0], expiration = dates[1];
		/*console.log(dates);*/
		if(moment(dates[1]).format("YYYY-MM-DD") > moment().format("YYYY-MM-DD")){
			$("#product-validity-"+product_id).html("Valide du <br><span class='highlighted-value'> "+moment(activation).format("DD/MM/YYYY")+"</span><br>au<br><span class='highlighted-value'>"+moment(expiration).format("DD/MM/YYYY")+"</span>");
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Valide du <span>"+moment(activation).format("DD/MM/YYYY")+"</span> au <span>"+moment(expiration).format("DD/MM/YYYY")+"</span>");
			$("#purchase-item-"+product_id).removeClass("item-pending");
			$("#purchase-item-"+product_id).removeClass("item-expired");
			$("#purchase-item-"+product_id).addClass("item-active");
			$("#btn-activate-"+product_id).html("<span class='glyphicon glyphicon-ban-circle'></span> Désactiver");
			document.getElementById("btn-activate-"+product_id).onclick = function(){ deactivateProduct(product_id); };
			$("#btn-activate-"+product_id).removeClass("trigger-sub");
			$("#btn-activate-"+product_id).attr("data-argument", null);
			$("#btn-activate-"+product_id).attr("data-subtype", null);
		} else {
			$("#product-validity-"+product_id).html("<span class='highlighted-value'>Activé</span><br>le "+moment(activation).format("DD/MM/YYYY")+"<br><span class='highlighted-value'>Expiré</span><br>le "+moment(expiration).format("DD/MM/YYYY"));
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Expiré le "+moment(expiration).format("DD/MM/YYYY"));
			$("#purchase-item-"+product_id).removeClass("item-pending");
			if(dates[2] < 0){
				$("#purchase-item-"+product_id).addClass("item-overused");
			} else {
				$("#purchase-item-"+product_id).addClass("item-expired");
			}
			$("#btn-activate-"+product_id).html("<span class='glyphicon glyphicon-play-circle'></span> Réactiver");
			$("#btn-activate-"+product_id).attr("data-argument", product_id);
			$("#btn-activate-"+product_id).attr("data-subtype", "activate");
			$("#btn-activate-"+product_id).addClass("trigger-sub");
			$("btn-activate-"+product_id).prop('onclick', null).off('click');
		}
		$(".sub-modal").hide();
		computeRemainingHours(product_id, true);
	})
}

/** Compute the remaining hours of a product **/
function computeRemainingHours(product_id, refresh){
	console.log("Computing product "+product_id);
	$.post("functions/compute_remaining_hours.php", {product_id : product_id}).done(function(value){
		/** Things to do here:
			This function checks everything and sets everything if needed. It computes the remaining hours of a product, its status, it activates or deactivates if necessary and computes the activation and expiration dates as well. Once it's done, it'll call the function to refresh the list of sessions so they stay up-to-date as well.
			This is the all-purpose script that pretty much does everything for a product.
		**/
		/*console.log(value);*/
		$("#purchase-item-"+product_id).removeClass("item-pending");
		$("#purchase-item-"+product_id).removeClass("item-overused");
		$("#purchase-item-"+product_id).removeClass("item-active");
		$("#purchase-item-"+product_id).removeClass("item-expired");
		$("#purchase-item-"+product_id).removeClass("item-consumed");
		var values = JSON.parse(value);
		console.log(values);
		var date = "-", activation = "-", usage = "-", hours = values.hours, status = values.status, limit = values.limit;
		if(values.expiration != null){ var date = moment(values.expiration).format("DD/MM/YYYY"); }
		if(values.activation != null){ var activation = moment(values.activation).format("DD/MM/YYYY"); }
		if(values.usage != null){ var usage = moment(values.usage).format("DD/MM/YYYY"); }

		if(status == 2){ // If the product is expired
			if(hours < 0){ // If the product is overused
				$("#product-status-"+product_id).html("<span class='highlighted-value'>"+ -1 * hours + " heures</span> de consommation excessive");
				$("#purchase-item-"+product_id+">p.purchase-product-hours").html(-1 * hours + " heures en excès");
				$("#purchase-item-"+product_id).addClass("item-overused");
				$(".usage-slot-date").text(usage);
			} else if(hours > 0){ // If the product still has remaining hours
				$("#product-status-"+product_id).html("<span class='highlighted-value'>"+ hours + " heures</span> restantes");
				if(limit != '1'){
					$("#purchase-item-"+product_id+">p.purchase-product-hours").html(hours + " heures restantes");
				}
				$(".usage-slot-date").text("-");
				$("#purchase-item-"+product_id).addClass("item-expired");
			} else { // If the product has no more hours.
				$("#product-status-"+product_id).html("<span class='highlighted-value'>"+ hours + " heures</span> restantes");
				$("#purchase-item-"+product_id+">p.purchase-product-hours").html("Heures épuisées");
				$("#purchase-item-"+product_id).addClass("item-expired");
				$(".usage-slot-date").text(usage);
			}
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Expiré le "+date);
			$(".activation-slot-date").text(activation);
			$(".expiration-slot-date").text(date);
		} else if (status == 1){ // If the product is active
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Valide du "+activation+" au "+date);
			$("#product-status-"+product_id).html("<span class='highlighted-value'>"+ hours + " heures</span> restantes");
			if(limit != '1'){
				$("#purchase-item-"+product_id+">p.purchase-product-hours").html(hours + " heures restantes");
			}
			$("#purchase-item-"+product_id).addClass("item-active");
			$(".activation-slot-date").text(activation);
			$(".expiration-slot-date").text(date);
			$(".usage-slot-date").text("-");
		} else { // If the product is pending
			$("#product-status-"+product_id).html("<span class='highlighted-value'>En attente</span>");
			if(limit != '1'){
				$("#purchase-item-"+product_id+">p.purchase-product-hours").html(hours + " heures restantes");
			}
			$("#product-validity-"+product_id).html("<span class='highlighted-value'>En attente</span>");
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("En attente");
			$("#purchase-item-"+product_id).addClass("item-pending");
			$(".activation-slot-date").text("-");
			$(".expiration-slot-date").text("-");
			$(".usage-slot-date").text("-");
		}
		if(refresh){
			console.log("Refreshing participations");
			(function(){
				$.when(fetchProduct(product_id), fetchSessions(product_id)).done(function(product, sessions){
					fillSessions(sessions);
					console.log("Partcipations refreshed");
				});
			})();
		}
		console.log("Computing done.");
	})
}

function deactivateProduct(product_id, value){
	$.post("functions/deactivate_product.php", {product_id : product_id, value : value}).done(function(data){
		var value = JSON.parse(data);
		if(value == 0){
			$("#product-validity-"+product_id).html("<span class='highlighted-value'>En attente</span><br>d'activation");
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("En attente");
			$("#purchase-item-"+product_id).addClass("item-pending");
			$("#purchase-item-"+product_id).removeClass("item-expired");
			$("#btn-activate-"+product_id).html("<span class='glyphicon glyphicon-play-circle'></span> Activer");
		} else {
			$("#product-validity-"+product_id).html("<span class='highlighted-value'>Expiré</span><br>le "+moment(value).format("DD/MM/YYYY"));
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Expiré le "+moment(value).format("DD/MM/YYYY"));
			$("#purchase-item-"+product_id).removeClass("item-pending");
			$("#purchase-item-"+product_id).addClass("item-expired");
			$("#btn-activate-"+product_id).html("<span class='glyphicon glyphicon-play-circle'></span> Réactiver");
		}
		$("#purchase-item-"+product_id).removeClass("item-active");
		/*document.getElementById("btn-activate-"+product_id).onclick = function(){ activateProduct(product_id); };*/
		$("#btn-activate-"+product_id).attr("data-argument", product_id);
		$("#btn-activate-"+product_id).attr("data-subtype", "activate");
		$("#btn-activate-"+product_id).addClass("trigger-sub");
		$("btn-activate-"+product_id).prop('onclick', null).off('click');
	})
}

function deleteParticipation(participation_id){
	$.post("functions/delete_participation.php", {record_id : participation_id}).done(function(old_product){
		$(".sub-modal").hide();
		var re = /historique/i;
		if(top.location.pathname === '/Salsabor/regularisation/participations' || re.exec(top.location.pathname) != null){
			$(".irregulars-target-container").empty();
			$("#participation-"+participation_id).remove();
			computeRemainingHours(old_product, false);
			$("#total-count").text($(".product-participation").length);
			$("#valid-count").text($(".participation-valid").length);
			$("#over-count").text($(".participation-over").length);
		} else {
			computeRemainingHours(old_product, true);
		}
	})
}

function deleteProduct(product_id){
	console.log(product_id);
	$.post("functions/delete_product.php", {product_id : product_id}).done(function(data){
		$("#purchase-item-"+product_id).remove();
		if(data != null){
			$("#purchase-"+data).remove();
		}
		$(".sub-modal").hide();
	})
}

function extendProduct(product_id, end_date){
	$.post("functions/extend_product.php", {product_id : product_id, end_date : end_date}).done(function(){
		if(end_date == null){
			$("#btn-arep").attr("data-arep", "null");
		} else {
			$("#btn-arep").attr("data-arep", end_date);
		}
		computeRemainingHours(product_id, true);
		$(".sub-modal").hide();
	})
}

/** Fetch the products that can be target of a record reassignment **/
function fetchEligibleProducts(argument_id, argument_type){
	return $.post("functions/fetch_user_products.php", {argument_id : argument_id, type : argument_type});
}
function displayEligibleProducts(data){
	var products_list = JSON.parse(data), product_status, product_flavor_text, product_hours, product_purchase_date;
	var body = "<ul class='purchase-inside-list'>";
	if(products_list.length == 0){
		body += "Aucun produit n'est disponible";
	} else{
		for(var i = 0; i < products_list.length; i++){
			product_purchase_date = "Acheté le "+moment(products_list[i].transaction_achat).format("DD/MM/YYYY");
			switch(products_list[i].status){
				case '1':
					product_status = "item-active";
					product_flavor_text = "Valide du "+moment(products_list[i].start).format("DD/MM/YYYY")+" au "+moment(products_list[i].validity).format("DD/MM/YYYY");
					break;

				case '2':
					product_status = "item-expired";
					product_flavor_text = "Valide du "+moment(products_list[i].start).format("DD/MM/YYYY")+" au "+moment(products_list[i].validity).format("DD/MM/YYYY");
					break;

				case '0':
					product_status = "item-pending";
					product_flavor_text = "En attente";
					break;
			}
			if(products_list[i].hours < 0 && products_list[i].unlimited != 1){
				product_status = "item-overused";
			}
			body += "<li class='sub-modal-product "+product_status+"' data-argument='"+products_list[i].id+"'>";
			body += "<p class='smp-title'>"+products_list[i].title+"</p>";
			body += "<p>"+product_purchase_date+"</p>";
			body += "<p>"+product_flavor_text+"</p>";
			if(products_list[i].unlimited != 1){
				if(products_list[i].hours < 0){
					product_hours = -1 * products_list[i].hours+" heures en excès";
				} else {
					product_hours = 1 * products_list[i].hours+" heures restantes";
				}
				body += "<p>"+product_hours+"</p>";
			}
			body += "</li>";
		}
	}
	body += "</ul>";
	return body;
}

/** Fetch the purchase : products and maturities of the purchase **/
function fetchMaturities(purchase_id){
	return $.post("functions/fetch_maturities.php", {purchase_id : purchase_id});
}

function fetchProduct(product_id){
	return $.get("functions/fetch_product.php", {product_id : product_id});
}

function fetchPurchase(purchase_id){
	if($("#body-purchase-"+purchase_id).hasClass("in")){
		$("#body-purchase-"+purchase_id).collapse("hide");
		$("#body-purchase-"+purchase_id).empty();
	} else {
		$.when(fetchSubs(purchase_id), fetchMaturities(purchase_id)).done(function(data1, data2){
			var purchase_list = JSON.parse(data1[0]);
			var maturities_list = JSON.parse(data2[0]);

			// Handle purchases
			var contents = "<p class='purchase-subtitle'>Liste des produits</p>";
			contents += "<div class='row purchase-product-list-container' id='products-"+purchase_id+"'>";
			contents += "<ul class='purchase-inside-list purchase-product-list'>";
			for(var i = 0; i < purchase_list.length; i++){
				var item_status, text_status;
				// Status of the purchase
				if(purchase_list[i].status == '0'){ // If not yet activated
					item_status = "item-pending";
					text_status = "En attente";
				} else if(purchase_list[i].status == '2'){ // If expired or fully used
					item_status = "item-expired";
					text_status = "Expiré le "+moment(purchase_list[i].validity).format("DD/MM/YYYY");
				} else { // If active or set to activate in the near future
					item_status = "item-active";
					text_status = "Valide du <span> "+moment(purchase_list[i].activation).format("DD/MM/YYYY")+"</span> au <span>"+moment(purchase_list[i].validity).format("DD/MM/YYYY")+"</span>";
				}
				contents += "<li class='purchase-item panel-item "+item_status+" container-fluid' id='purchase-item-"+purchase_list[i].id+"' data-toggle='modal' data-target='#product-modal' data-argument='"+purchase_list[i].id+"'>";
				contents += "<p class='col-lg-12 panel-item-title bf'>"+purchase_list[i].product+"</p>";
				contents += "<div class='purchase-product-subdetails'>";
				contents += "<p class='col-lg-3 purchase-product-validity'>";
				contents += text_status;
				contents += "</p>";
				contents += "<p class='col-lg-3 purchase-product-user'>"+purchase_list[i].user+"</p>";
				contents += "<p class='col-lg-3 purchase-product-hours'>";
				if(purchase_list[i].flag_hours == 1){
					if(purchase_list[i].remaining_hours < 0){
						contents += -1 * purchase_list[i].remaining_hours+" heures en excès";
					} else {
						contents += 1 * purchase_list[i].remaining_hours+" heures restatntes";
					}
				}
				contents += "</p>";
				contents += "<p class='col-lg-1 purchase-product-price align-right'>";
				contents += purchase_list[i].price;
				contents += " €</p>";
				contents += "</div>";
				contents += "</li>";
				/*contents += "<div class='purchase-item-options'>Opérations sur le produit acheté</div>";*/
			}
			contents += "</ul></div>";

			// Handle maturities
			contents += "<p class='purchase-subtitle'>Echéancier</p>";
			contents += "<div class='row purchase-maturities-container' id='maturities-"+purchase_id+"'>";
			contents += "<ul class='purchase-inside-list maturities-list'>";
			for(var i = 0; i < maturities_list.length; i++){
				contents += "<li class='purchase-item panel-item maturity-item container-fluid'>";
				contents += "<p class='col-lg-1'>"+moment(maturities_list[i].date).format("DD/MM/YYYY")+"</p>";
				contents += "<p class='col-lg-1'>"+maturities_list[i].price+" €</p>";
				contents += "<p class='col-lg-2'>"+maturities_list[i].method+"</p>";
				contents += "<p class='col-lg-2'>"+maturities_list[i].payer+"</p>";
				if(maturities_list[i].reception_status == '1'){
					contents += "<p class='col-lg-1 status-icon icon-success' id='icon-reception-"+maturities_list[i].id+"' title='Annuler réception' onClick='uncheckReception("+maturities_list[i].id+")'><span class='glyphicon glyphicon-ok'></span></p>";
					contents += "<p class='col-lg-1' id='date-reception-"+maturities_list[i].id+"'>"+moment(maturities_list[i].date_reception).format("DD/MM/YYYY")+"</p>";
				} else {
					contents += "<p class='col-lg-1 status-icon' id='icon-reception-"+maturities_list[i].id+"' title='Valider réception' onClick='checkReception("+maturities_list[i].id+")'><span class='glyphicon glyphicon-ok'></span></p>";
					contents += "<p class='col-lg-1' id='date-reception-"+maturities_list[i].id+"'>En attente</p>";
				}
				if(maturities_list[i].bank_status == '1'){
					contents += "<p class='col-lg-1 status-icon icon-success' id='icon-bank-"+maturities_list[i].id+"' title='Annuler encaissement' onClick='uncheckBank("+maturities_list[i].id+")'><span class='glyphicon glyphicon-download-alt'></span></p>";
					contents += "<p class='col-lg-1' id='date-bank-"+maturities_list[i].id+"'>"+moment(maturities_list[i].date_bank).format("DD/MM/YYYY")+"</p>";
				} else {
					contents += "<p class='col-lg-1 status-icon' id='icon-bank-"+maturities_list[i].id+"' title='Valider encaissement' onClick='checkBank("+maturities_list[i].id+")'><span class='glyphicon glyphicon-download-alt'></span></p>";
					contents += "<p class='col-lg-1' id='date-bank-"+maturities_list[i].id+"'>En attente</p>";
				}
				contents += "</li>";
			}
			contents += "</ul></div>";
			$("#body-purchase-"+purchase_id).append(contents);
			$("#body-purchase-"+purchase_id).collapse("show");
		})
	}
}

function fetchSessions(product_id){
	/** Fetch the details of a product : product and all the sessions of this product **/
	return $.get("functions/fetch_sessions_product.php", {product_id : product_id});
}

function fetchSubs(purchase_id){
	return $.post("functions/fetch_subscriptions.php", {purchase_id : purchase_id});
}

function fillSessions(sessions){
	$(".participations-list").empty();
	/*console.log(sessions);*/
	var sessions_list = JSON.parse(sessions[0]), valid_sessions = "", over_sessions = "", out_sessions = "", previousSessions = [], valid_indicator = -1, over_indicator = -1;
	for(var i = 0; i < sessions_list.length; i++){
		/*console.log(sessions_list[i]);*/
		if(sessions_list[i].valid == 2){
			previousSessions.push(i);
			over_indicator = -2;
		} else {
			/*console.log(sessions_list[i]);*/
			if(valid_indicator == -1){
				valid_sessions += "<p id='over-session-alert'>Cours validés :</p>";
				valid_indicator = 0;
			}
			valid_sessions += "<li class='product-participation participation-valid container-fluid' data-argument='"+sessions_list[i].id+"' id='participation-"+sessions_list[i].id+"'>";
			valid_sessions += "<p class='col-lg-12 session-title'>"+sessions_list[i].title+"</p>";
			valid_sessions += "<p class='col-lg-12 session-hours'>"+moment(sessions_list[i].start).format("DD/MM/YYYY")+" : "+moment(sessions_list[i].start).format("HH:mm")+" - "+moment(sessions_list[i].end).format("HH:mm")+"</p>";
			valid_sessions += "</li>";
		}
		/*if(product_details.status == '2' && product_details.flag_hours == '0'){
			var product_validity = "<p id='product-status"+product_details.id+"'><span class='highlighted-value'>"+hoursUsed+"</span><br> heures consommées</p>";
		}*/
	}
	for(var j = 0; j < previousSessions.length; j++){
		if(over_indicator == -2){
			out_sessions += "<p id='over-session-alert'>Cours hors forfait :</p>";
			over_indicator = 0;
		}
		out_sessions += "<li class='product-participation participation-over container-fluid' data-argument='"+sessions_list[previousSessions[j]].id+"' id='participation-"+sessions_list[previousSessions[j]].id+"'>";
		out_sessions += "<p class='col-lg-12 session-title'>"+sessions_list[previousSessions[j]].title+"</p>";
		out_sessions += "<p class='col-lg-12 session-hours'>"+moment(sessions_list[previousSessions[j]].start).format("DD/MM/YYYY")+" : "+moment(sessions_list[previousSessions[j]].start).format("HH:mm")+" - "+moment(sessions_list[previousSessions[j]].end).format("HH:mm")+"</p>";
		out_sessions += "</li>";
	}
	$(".participations-list").append("<h2 class='modal-body-title'>Liste des cours</h2>");
	$(".participations-list").append("<ul class='purchase-inside-list'>"+out_sessions+over_sessions+valid_sessions+"</ul>");
}

function fetchSingleParticipation(participation_id){
	return $.get("functions/fetch_single_participation.php", {participation_id : participation_id});
}
function displaySingleParticipation(participation_details){
	var participation_details = JSON.parse(participation_details);
	$("#participation-"+participation_details.id).removeClass("participation-over");
	$("#participation-"+participation_details.id).removeClass("participation-valid");
	var participation = "<div class='col-lg-4'>";
	participation += "<p class='col-lg-12 session-title'>"+participation_details.cours_name+"</p>";
	participation += "<p class='col-lg-12 session-hours'>"+moment(participation_details.date).format("DD/MM/YYYY")+" : "+moment(participation_details.hour_start).format("HH:mm")+" -  "+moment(participation_details.hour_end).format("HH:mm")+"</p>";
	participation += "</div>";
	participation += "<div class='col-lg-8'>";
	if(participation_details.product == null){
		participation += "<p class='col-lg-12 session-title'>Pas de produit associé</p>";
		participation += "<p class='col-lg-12 session-hours'>Cliquez pour chercher un produit à associer</p>";
		$("#participation-"+participation_details.id).addClass("participation-over");
	} else {
		participation += "<p class='col-lg-12 session-title'>"+participation_details.product_name+"</p>";
		participation += "<p class='col-lg-12 session-hours'>Acheté le "+moment(participation_details.achat).format("DD/MM/YYYY")+" / Valide du "+moment(participation_details.product_activation).format("DD/MM/YYYY")+" au "+moment(participation_details.product_validity).format("DD/MM/YYYY")+"</p>";
		$("#participation-"+participation_details.id).addClass("participation-valid");
	}
	participation += "</div>";
	$("#participation-"+participation_details.id).html(participation);
}

function link(map, index){
	if(map.length > index){
		setTimeout(function(){
			reportSession(null, map[index]);
			link(map, ++index);
		}, 700);
	} else {
		var re = /historique/i;
		if(top.location.pathname == "/Salsabor/regularisation/participations" || re.exec(top.location.pathname) != null){
			$("#link-all").html("<span class='glyphicon glyphicon-arrow-right'></span> Associer toutes les participations irrégulières");
		} else {
			$("#link-all").html("<span class='glyphicon glyphicon-arrow-right'></span> Trouver assoc.");
		}
	}
}

function linkAll(){
	// This function will try to find the correct product for every invalid participation.
	if(top.location.pathname == "/Salsabor/regularisation/participations"){
		var invalidMap = $(".irregular-participation").map(function(){
			return this.dataset.argument;
		}).get();
	} else {
		var re = /historique/i;
		if(re.exec(top.location.pathname) != null){
			var invalidMap = $(".participation-over").map(function(){
				return this.dataset.argument;
			}).get().reverse();
		} else {
			var invalidMap = $(".participation-over").map(function(){
				return this.dataset.argument;
			}).get();
		}
	}
	$("#link-all").text("En cours...");
	link(invalidMap, 0);
}

function reportSession(target_product_id, participation_id){
	if(target_product_id == null){
		console.log("No product has been indicated for participation "+participation_id+". Finding product...");
	}
	$.post("functions/set_product_session.php", {participation_id : participation_id, product_id : target_product_id}).done(function(data){
		var products = JSON.parse(data), old_product = products.old_product, new_product = products.new_product;
		if(new_product != null){
			console.log("A target product has been found: "+products.new_product);
			computeRemainingHours(new_product, false);
		} else {
			console.log("No product has been found");
		}
		$(".sub-modal").hide();
		var re = /historique/i;
		if(re.exec(top.location.pathname) != null){
			if(new_product != null){
				$.when(fetchSingleParticipation(participation_id)).done(function(participation){
					displaySingleParticipation(participation);
					$("#total-count").text($(".product-participation").length);
					$("#valid-count").text($(".participation-valid").length);
					$("#over-count").text($(".participation-over").length);
				});
			}
			if(old_product != null) computeRemainingHours(old_product, false);
		} else {
			if(old_product != null) computeRemainingHours(old_product, true);
		}
		if(top.location.pathname === '/Salsabor/regularisation/participations' && new_product != null){
			if($("#participation-"+participation_id).next().is("a") && $("#participation-"+participation_id).prev().is("a")){
				$("#participation-"+participation_id).prev().remove();
			}
			$("#participation-"+participation_id).remove();
			$(".irregulars-target-container").empty();
			if(old_product != null) computeRemainingHours(old_product, true);
		}
	})
}

function unlinkAll(){
	// This function will find all invalid participations (identified in display by .participation-over) and log their data-argument
	var invalidMap = $(".participation-over").map(function(){
		unlinkParticipation(this.dataset.argument);
	});
}

function toggleBoolean(button, boolean_name, product_id, old_value){
	$.post("functions/set_boolean.php", {boolean_name : boolean_name, product_id : product_id, old_value : old_value}).done(function(){
		if(old_value == 0){ // Then the new value is 1.
			button.removeClass("status-disabled");
			button.addClass("status-enabled");
			button.children("span").removeClass("glyphicon-floppy-remove");
			button.children("span").addClass("glyphicon-floppy-saved");
			document.getElementById(button.attr("id")).dataset.boolean = 1;
			computeRemainingHours(product_id, true);
			if(button.attr("id") == "auto_status"){
				$("#manual-expire").removeClass("enabled");
				$("#manual-expire").addClass("disabled");
			}
		} else {
			button.removeClass("status-enabled");
			button.addClass("status-disabled");
			button.children("span").removeClass("glyphicon-floppy-saved");
			button.children("span").addClass("glyphicon-floppy-remove");
			document.getElementById(button.attr("id")).dataset.boolean = 0;
			if(button.attr("id") == "auto_status"){
				$("#manual-expire").removeClass("disabled");
				$("#manual-expire").addClass("enabled");
			}
		}
	})
}

function unlinkParticipation(participation_id){
	// This function is used when a session has to be delinked from its product.
	$.post("functions/unlink_participation.php", {participation_id : participation_id}).done(function(old_product){
		$(".sub-modal").hide();
		var re = /historique/i;
		if(re.exec(top.location.pathname) != null){
			computeRemainingHours(old_product, false);
			$.when(fetchSingleParticipation(participation_id)).done(function(participation){
				displaySingleParticipation(participation);
				$("#valid-count").text($(".participation-valid").length);
				$("#over-count").text($(".participation-over").length);
			});
		} else {
			computeRemainingHours(old_product, true);
		}
	})
}
