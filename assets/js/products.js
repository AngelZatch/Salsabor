$(document).ready(function(){
	moment.locale('fr');
	$("#product-modal").on('show.bs.modal', function(event){
		var argument = $(event.relatedTarget).data('argument'), modal = $(this);

		$.when(fetchProduct(argument), fetchSessions(argument)).done(function(product, sessions){
			var product_details = JSON.parse(product[0]), sessions_list = JSON.parse(sessions[0]), buttons = "";

			// Handling the product
			modal.find(".modal-title").text(product_details.product+" (ID : "+product_details.id+")");
			modal.find(".purchase-sub").text("Transaction "+product_details.transaction);

			var product_status = "<p id='product-validity-"+product_details.id+"'>";
			switch(product_details.status){
				case '0':
					product_status += "<span class='highlighted-value'>En attente</span> d'activation";
					/*buttons += "<button class='btn btn-default btn-block btn-modal' id='btn-activate-"+product_details.id+"' onclick='activateProduct("+product_details.id+")'><span class='glyphicon glyphicon-play-circle'></span> Activer</button>";*/
					// Activation button
					buttons += "<button class='btn btn-default btn-block btn-modal trigger-sub' id='btn-activate-"+product_details.id+"' data-argument='"+product_details.id+"' data-subtype='activate'><span class='glyphicon glyphicon-play-circle'></span> Activer</button>";
					break;

				case '1':
					product_status += "Valide du <br><span class='highlighted-value'> "+moment(product_details.activation).format("DD/MM/YYYY")+"</span><br>au<br><span class='highlighted-value'>"+moment(product_details.validity).format("DD/MM/YYYY")+"</span>";
					// Deactivation button
					buttons += "<button class='btn btn-default btn-block btn-modal' id='btn-activate-"+product_details.id+"' onclick='deactivateProduct("+product_details.id+")'><span class='glyphicon glyphicon-ban-circle'></span> Désactiver</button>";
					// Extension button
					buttons += "<button class='btn btn-default btn-block btn-modal trigger-sub' id='btn-arep' data-argument='"+product_details.id+"' data-subtype='AREP'><span class='glyphicon glyphicon-calendar'></span> AREP</button>";
					break;

				case '2':
					product_status += "<span class='highlighted-value'>Expiré</span><br>le "+moment(product_details.validity).format("DD/MM/YYYY");
					/*buttons += "<button class='btn btn-default btn-block btn-modal' id='btn-activate-"+product_details.id+"' onclick='activateProduct("+product_details.id+")'><span class='glyphicon glyphicon-play-circle'></span> Réactiver</button>";*/
					// Reactivation button
					buttons += "<button class='btn btn-default btn-block btn-modal trigger-sub' id='btn-activate-"+product_details.id+"' data-argument='"+product_details.id+"' data-subtype='activate'><span class='glyphicon glyphicon-play-circle'></span> Réactiver</button>";
					break;
			}
			product_status += "</p>";
			modal.find(".product-status").html(product_status);
			modal.find(".sessions-list").empty();
			if(product_details.flag_hours == '1'){ // If the product is not an illimited, a private lesson or an annual subscription
				var product_validity = "<p id='product-status-"+product_details.id+"'><span class='highlighted-value'>"+product_details.remaining_hours+" heures</span><br>restantes avant expiration</p>";

				// Computing hours button
				buttons += "<button class='btn btn-default btn-block btn-modal' onclick='computeRemainingHours("+product_details.id+")'><span class='glyphicon glyphicon-scale'></span> Recalculer</button>";
			} else {
				if(product_details.status == '1'){ // If the product is active
					var product_validity = "<p id='product-status"+product_details.id+"'><span class='highlighted-value'>"+moment(product_details.validity).toNow(true)+"</span><br> restants avant expiration</p>";
				}
			}
			if(product_details.subscription == '0'){ // If the product is NOT an annual subscription
				// Handling the sessions
				var totalHours = product_details.hours, valid_sessions = "", over_sessions = "";
				if(product_details.status == '2' && product_details.flag_hours == '0'){
					console.log("Forfait illimité expiré");
					var hoursUsed = 0;
				}
				for(var i = 0; i < sessions_list.length; i++){
					/*console.log(sessions_list[i]);*/
					if(product_details.illimited != '1'){
						if(totalHours > 0){
							if(i == 0){
								valid_sessions += "<p id='over-session-alert'>Cours validés :</p>";
							}
							valid_sessions += "<li class='product-session session-valid container-fluid' data-argument='"+sessions_list[i].id+"' id='session-"+sessions_list[i].id+"'>";
							valid_sessions += "<p class='col-lg-12 session-title'>"+sessions_list[i].title+"</p>";
							valid_sessions += "<p class='col-lg-12 session-hours'>"+moment(sessions_list[i].start).format("DD/MM/YYYY")+" : "+moment(sessions_list[i].start).format("HH:mm")+" - "+moment(sessions_list[i].end).format("HH:mm")+"</p>";
							valid_sessions += "</li>";
						} else {
							if(totalHours == 0){
								over_sessions += "<p id='over-session-alert'>Cours hors forfait :</p>";
							}
							over_sessions += "<li class='product-session session-over container-fluid' data-argument='"+sessions_list[i].id+"' id='session-"+sessions_list[i].id+"'>";
							over_sessions += "<p class='col-lg-12 session-title'>"+sessions_list[i].title+"</p>";
							over_sessions += "<p class='col-lg-12 session-hours'>"+moment(sessions_list[i].start).format("DD/MM/YYYY")+" : "+moment(sessions_list[i].start).format("HH:mm")+" - "+moment(sessions_list[i].end).format("HH:mm")+"</p>";
							over_sessions += "</li>";
						}
						totalHours -= sessions_list[i].duration;
					} else {
						if(i == 0){
							valid_sessions += "<p id='over-session-alert'>Cours validés :</p>";
						}
						valid_sessions += "<li class='product-session session-valid container-fluid' data-argument='"+sessions_list[i].id+"' id='session-"+sessions_list[i].id+"'>";
						valid_sessions += "<p class='col-lg-12 session-title'>"+sessions_list[i].title+"</p>";
						valid_sessions += "<p class='col-lg-12 session-hours'>"+moment(sessions_list[i].start).format("DD/MM/YYYY")+" : "+moment(sessions_list[i].start).format("HH:mm")+" - "+moment(sessions_list[i].end).format("HH:mm")+"</p>";
						valid_sessions += "</li>";
						if(product_details.status == '2' && product_details.flag_hours == '0'){
							hoursUsed += parseFloat(sessions_list[i].duration);
						}
					}
					if(product_details.status == '2' && product_details.flag_hours == '0'){
						var product_validity = "<p id='product-status"+product_details.id+"'><span class='highlighted-value'>"+hoursUsed+"</span><br> heures consommées</p>";
					}
				}
				modal.find(".sessions-list").append("<ul class='purchase-inside-list'>"+over_sessions+valid_sessions+"</ul>");
			}
			modal.find(".product-validity").html(product_validity);
			modal.find(".modal-actions").html(buttons);
		})
	}).on('hidden.bs.modal', function(event){
		$(".sub-modal").hide();
	})
}).on('click', '.trigger-sub', function(e){
	var target = document.getElementById($(this).attr("id"));
	var tpos = $(this).position();
	var product_id = target.dataset.argument;
	var type = target.dataset.subtype;
	console.log(product_id, type);

	var title, body = "", footer = "";
	switch(type){
		case 'AREP':
			title = "Prolonger";
			body += "<input type='text' class='form-control datepicker'/>";
			footer += "<button class='btn btn-success extend-product' data-argument='"+product_id+"' id='btn-sm-extend'>Prolonger</button>";
			$(".sub-modal").css({top : tpos.top+51+'px'});
			$(".sub-modal-body").html(body);
			break;

		case 'activate':
			title = "Activer";
			body += "<input type='text' class='form-control datepicker'/>";
			footer += "<button class='btn btn-success activate-product' data-argument='"+product_id+"' id='btn-sm-activate'>Activer</button>";
			$(".sub-modal").css({top : tpos.top+51+'px'});
			$(".sub-modal-body").html(body);
			break;

		case 'report':
			title = "Assigner à un autre produit";
			var record_id = product_id;
			$.post("functions/fetch_user_products.php", {record_id : record_id}).done(function(data){
				var products_list = JSON.parse(data), product_status;
				body += "<ul class='purchase-inside-list'>";
				for(var i = 0; i < products_list.length; i++){
					if(products_list[i].status == '1'){
						product_status = "item-active";
					} else {
						product_status = "item-pending";
					}
					body += "<li class='sub-modal-product "+product_status+"' data-argument='"+products_list[i].id+"'>"+products_list[i].title+"</li>";
				}
				body += "</ul>";
				$(".sub-modal-body").html(body);
			})
			footer += "<button class='btn btn-success report-product' id='btn-product-report' data-session='"+record_id+"'>Reporter</button>";
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
	$(".sub-modal").toggle(0);
}).on('click', '.activate-product', function(){
	var date = moment($(".datepicker").val(),"DD/MM/YYYY").format("YYYY-MM-DD");
	var product_id = document.getElementById($(this).attr("id")).dataset.argument;
	activateProductWithDate(product_id, date);
}).on('click', '.extend-product', function(){
	var date = moment($(".datepicker").val(),"DD/MM/YYYY").format("YYYY-MM-DD");
	var product_id = document.getElementById($(this).attr("id")).dataset.argument;
	extendProduct(product_id, date);
}).on('click', '.product-session', function(){
	var session = $(this);
	var product_id = document.getElementById($(this).attr("id")).dataset.argument;
	if(!$(this).hasClass("options-shown")){
		session.addClass("options-shown");
		var content = "<div class='session-options'><button class='btn btn-default btn-modal trigger-sub' data-argument='"+product_id+"' data-subtype='report' id='btn-session-report'><span class='glyphicon glyphicon-arrow-right'></span> Affecter à un autre produit</button></div>";
		session.append(content);
	} else {
		$(this).find(".session-options").remove();
		session.removeClass("options-shown");
	}
}).on('click', '.sub-modal-product', function(){
	$(".sub-modal-product>span").remove();
	$(".sub-modal-product").attr("id", "");
	$(this).append("<span class='glyphicon glyphicon-ok'></span>");
	$(this).attr("id", "product-selected");
}).on('click', '.report-product', function(){
	var session_target = document.getElementById($(this).attr("id")).dataset.session;
	var product_target = document.getElementById("product-selected").dataset.argument;
	reportSession(product_target, session_target);
})

/** Fetch the purchase : products and maturities of the purchase **/
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
					if(purchase_list[i].activation > moment().format("YYYY-MM-DD")){
						item_status = "item-near-activation";
					} else if(purchase_list[i].validity < moment().add(5, 'day').format("YYYY-MM-DD")){
						item_status = "item-near-expiration";
					} else {
						item_status = "item-active";
					}
					text_status = "Valide du <span> "+moment(purchase_list[i].activation).format("DD/MM/YYYY")+"</span> au <span>"+moment(purchase_list[i].validity).format("DD/MM/YYYY")+"</span>";
				}
				contents += "<li class='purchase-item "+item_status+" container-fluid' id='purchase-item-"+purchase_list[i].id+"' data-toggle='modal' data-target='#product-modal' data-argument='"+purchase_list[i].id+"'>";
				contents += "<p class='col-lg-3 purchase-product-name'>"+purchase_list[i].product+"</p>";
				contents += "<p class='col-lg-3 purchase-product-validity'>";
				contents += text_status;
				contents += "</p>";
				contents += "<p class='col-lg-3 purchase-product-hours'>";
				if(purchase_list[i].flag_hours == 1){
					contents += purchase_list[i].remaining_hours+" heures restatntes";
				}
				contents += "</p>";
				contents += "<p class='col-lg-1 purchase-product-price align-right'>";
				contents += purchase_list[i].price;
				contents += " €</p>";
				contents += "</li>";
				/*contents += "<div class='purchase-item-options'>Opérations sur le produit acheté</div>";*/
			}
			contents += "</ul></div>";

			// Handle maturities
			contents += "<p class='purchase-subtitle'>Echéancier</p>";
			contents += "<div class='row purchase-maturities-container' id='maturities-"+purchase_id+"'>";
			contents += "<ul class='purchase-inside-list maturities-list'>";
			for(var i = 0; i < maturities_list.length; i++){
				contents += "<li class='purchase-item maturity-item container-fluid'>";
				contents += "<p class='col-lg-1'>"+moment(maturities_list[i].date).format("DD/MM/YYYY")+"</p>";
				contents += "<p class='col-lg-1'>"+maturities_list[i].price+" €</p>";
				contents += "<p class='col-lg-2'>"+maturities_list[i].method+"</p>";
				contents += "<p class='col-lg-2'>"+maturities_list[i].payer+"</p>";
				if(maturities_list[i].reception_status == '1'){
					contents += "<p class='col-lg-1 status-icon status-success' id='icon-reception-"+maturities_list[i].id+"' title='Annuler réception' onClick='uncheckReception("+maturities_list[i].id+")'><span class='glyphicon glyphicon-ok'></span></p>";
					contents += "<p class='col-lg-1' id='date-reception-"+maturities_list[i].id+"'>"+moment(maturities_list[i].date_reception).format("DD/MM/YYYY")+"</p>";
				} else {
					contents += "<p class='col-lg-1 status-icon' id='icon-reception-"+maturities_list[i].id+"' title='Valider réception' onClick='checkReception("+maturities_list[i].id+")'><span class='glyphicon glyphicon-ok'></span></p>";
					contents += "<p class='col-lg-1' id='date-reception-"+maturities_list[i].id+"'>En attente</p>";
				}
				if(maturities_list[i].bank_status == '1'){
					contents += "<p class='col-lg-1 status-icon status-success' id='icon-bank-"+maturities_list[i].id+"' title='Annuler encaissement' onClick='uncheckBank("+maturities_list[i].id+")'><span class='glyphicon glyphicon-download-alt'></span></p>";
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
function fetchSubs(purchase_id){
	return $.post("functions/fetch_subscriptions.php", {purchase_id : purchase_id});
}
function fetchMaturities(purchase_id){
	return $.post("functions/fetch_maturities.php", {purchase_id : purchase_id});
}

/** Fetch the details of a product : product and all the sessions of this product **/
function fetchProduct(product_id){
	return $.post("functions/fetch_product.php", {product_id : product_id});
}
function fetchSessions(product_id){
	return $.post("functions/fetch_sessions_product.php", {product_id : product_id});
}

/** Compute the remaining hours of a product **/
function computeRemainingHours(product_id){
	$.post("functions/compute_remaining_hours.php", {product_id : product_id}).done(function(value){
		/** Things to do here:
					- If the value is a number: update the value of remaining hours in the modal AND in the purchase space behind it
					- If the value is a date:
						-> update the value of remaining hours to 0 in the modal AND the purchase space behind it
						-> update the status of validity to "Expired on" + date in the modal AND the purchase space behind it
						-> Find all sessions taken with this product that happened AFTER the date and highlight them in red to indicate they cannot stay there and have to be assigned to another product.
					**/
		/*console.log(value);*/
		if(isNaN(value)){
			$("#product-status-"+product_id+">span.highlighted-value").text("0 heures");
			$("#purchase-item-"+product_id+">p.purchase-product-hours").html("Heures épuisées");
			$("#product-validity-"+product_id).html("<span class='highlighted-value'>Expiré</span><br>le "+moment(value).format("DD/MM/YYYY"));
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Expiré le "+moment(value).format("DD/MM/YYYY"));
			$("#purchase-item-"+product_id).removeClass("item-pending");
			$("#purchase-item-"+product_id).addClass("item-expired");
			$("#purchase-item-"+product_id).removeClass("item-active");
		} else {
			value = parseFloat(value).toFixed(2);
			$("#product-status-"+product_id+">span.highlighted-value").text(value+" heures");
			$("#purchase-item-"+product_id+">p.purchase-product-hours").html(value+" heures restantes");
			// Must update behind as well
		}
	})
}

/*function activateProduct(product_id){
	$.post("functions/activate_product.php", {product_id : product_id}).done(function(data){
		var dates = JSON.parse(data);
		console.log(dates);
		$("#product-validity-"+product_id).html("Valide du <br><span class='highlighted-value'> "+moment(dates[0]).format("DD/MM/YYYY")+"</span><br>au<br><span class='highlighted-value'>"+moment(dates[1]).format("DD/MM/YYYY")+"</span>");
		$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Valide du "+moment(dates[0]).format("DD/MM/YYYY")+" au "+moment(dates[1]).format("DD/MM/YYYY"));
		$("#purchase-item-"+product_id).removeClass("item-pending");
		$("#purchase-item-"+product_id).removeClass("item-expired");
		$("#purchase-item-"+product_id).addClass("item-active");
		$("#btn-activate-"+product_id).html("<span class='glyphicon glyphicon-ban-circle'></span> Désactiver");
		document.getElementById("btn-activate-"+product_id).onclick = function(){ deactivateProduct(product_id); };
	})
}*/

function activateProductWithDate(product_id, start_date){
	$.post("functions/activate_product.php", {product_id : product_id, start_date : start_date}).done(function(data){
		var dates = JSON.parse(data);
		/*console.log(dates);*/
		if(moment(dates[1]).format("YYYY-MM-DD") > moment().format("YYYY-MM-DD")){
			$("#product-validity-"+product_id).html("Valide du <br><span class='highlighted-value'> "+moment(dates[0]).format("DD/MM/YYYY")+"</span><br>au<br><span class='highlighted-value'>"+moment(dates[1]).format("DD/MM/YYYY")+"</span>");
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Valide du <span>"+moment(dates[0]).format("DD/MM/YYYY")+"</span> au <span>"+moment(dates[1]).format("DD/MM/YYYY")+"</span>");
			$("#purchase-item-"+product_id).removeClass("item-pending");
			$("#purchase-item-"+product_id).removeClass("item-expired");
			if(moment(dates[0]).format("YYYY-MM-DD") > moment().format("YYYY-MM-DD")){
				$("#purchase-item-"+product_id).addClass("item-near-activation");
			} else if(moment(dates[1]).format("YYYY-MM-DD") < moment().add(5, 'day').format("YYYY-MM-DD")){
				$("#purchase-item-"+product_id).addClass("item-near-expiration");
			} else {
				$("#purchase-item-"+product_id).addClass("item-active");
			}
			$("#btn-activate-"+product_id).html("<span class='glyphicon glyphicon-ban-circle'></span> Désactiver");
			document.getElementById("btn-activate-"+product_id).onclick = function(){ deactivateProduct(product_id); };
			$("#btn-activate-"+product_id).removeClass("trigger-sub");
			$("#btn-activate-"+product_id).attr("data-argument", null);
			$("#btn-activate-"+product_id).attr("data-subtype", null);
		} else {
			$("#product-validity-"+product_id).html("<span class='highlighted-value'>Expiré</span><br>le "+moment(dates[1]).format("DD/MM/YYYY"));
			$("#purchase-item-"+product_id+">p.purchase-product-validity").html("Expiré le "+moment(dates[1]).format("DD/MM/YYYY"));
			$("#purchase-item-"+product_id).removeClass("item-pending");
			$("#purchase-item-"+product_id).addClass("item-expired");
			$("#btn-activate-"+product_id).html("<span class='glyphicon glyphicon-play-circle'></span> Réactiver");
			$("#btn-activate-"+product_id).attr("data-argument", product_id);
			$("#btn-activate-"+product_id).attr("data-subtype", "activate");
			$("#btn-activate-"+product_id).addClass("trigger-sub");
			$("btn-activate-"+product_id).prop('onclick', null).off('click');
		}
		// Display the product as expired if the expiration date is prior to today's date.
	})
}

function deactivateProduct(product_id){
	$.post("functions/deactivate_product.php", {product_id : product_id}).done(function(data){
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
		$("#purchase-item-"+product_id).removeClass("item-near-activation");
		$("#purchase-item-"+product_id).removeClass("item-active");
		/*document.getElementById("btn-activate-"+product_id).onclick = function(){ activateProduct(product_id); };*/
		$("#btn-activate-"+product_id).attr("data-argument", product_id);
		$("#btn-activate-"+product_id).attr("data-subtype", "activate");
		$("#btn-activate-"+product_id).addClass("trigger-sub");
		$("btn-activate-"+product_id).prop('onclick', null).off('click');
	})
}

function extendProduct(product_id, end_date){
	$.post("functions/extend_product.php", {product_id : product_id, end_date : end_date}).done(function(){
		$("span.highlighted-value:nth-child(5)").text(moment(end_date).format("DD/MM/YYYY"));
		$("#purchase-item-"+product_id+">p.purchase-product-validity>span:nth-child(2)").html(moment(end_date).format("DD/MM/YYYY"));
	})
}

function reportSession(product_id, record_id){
	console.log(product_id, record_id);
	$.post("functions/set_product_session.php", {record_id : record_id, product_id : product_id}).done(function(old_product){
		$(".sub-modal").hide();
		/** Once a session has been assigned to another product, various things have to happen
		- Compute the remaning hours of the previous product : OK
		- Compute the remaining hours of the target product : OK
			-> Activate it if necessary
		- Remove the moved session out of the previous product : OK
		- Close the submodal : OK
		**/
		$("#session-"+record_id).remove();
		if(old_product != null){
			computeRemainingHours(old_product);
		}
		computeRemainingHours(product_id);
		if(top.location.pathname === '/Salsabor/regularisation'){
			$("#record-"+record_id).remove();
			$(".irregulars-target-container").empty();
		}
	})
}
