// bank_date is a moment() This function is only the visual feedback, since a maturity can be banked from different hooks.
function feedbackBankMaturity(maturity_id, bank_date){
	var icon = $("#bank-"+maturity_id);
	if(icon.hasClass("glyphicon-download-alt")){ // If the maturity was not banked
		icon.addClass("glyphicon-export");
		icon.removeClass("glyphicon-download-alt");
		icon.attr("title", "Décaisser l'échéance");

		$("#bank-span-"+maturity_id).text(bank_date.format("DD/MM/YYYY"));

		$("#maturity-"+maturity_id).removeClass("status-partial-success");
		$("#maturity-"+maturity_id).addClass("status-success");

		if(moment($("#deadline-maturity-span-"+maturity_id).text(), "DD/MM/YYYY") < moment())
			$("#deadline-maturity-span-"+maturity_id).removeClass("deadline-expired");

		// The maturity has been banked, it cannot be marked at "not received"
		$("#receive-"+maturity_id).addClass("disabled");
	} else {
		icon.addClass("glyphicon-download-alt");
		icon.removeClass("glyphicon-export");
		icon.attr("title", "Encaisser l'échéance");

		$("#bank-span-"+maturity_id).text("");

		$("#maturity-"+maturity_id).removeClass("status-success");
		$("#maturity-"+maturity_id).addClass("status-partial-success");

		$("#deadline-maturity-span-"+maturity_id).addClass("deadline-expired");

		// The maturity has been unbanked, it can again be marked at "not received"
		$("#receive-"+maturity_id).removeClass("disabled");
	}
}

function displayMaturities(maturities){
	var totalPrice = 0, contents = "";
	for(var i = 0; i < maturities.length; i++){
		var item_status = "", reception_date = "", bank_date = "", deadline_class = "", method = maturities[i].method;
		if(moment(maturities[i].date) < moment()){
			item_status = "status-over";
		}
		if(maturities[i].date_reception != ""){
			reception_date = moment(maturities[i].date_reception).format("DD/MM/YYYY");
			item_status = "status-partial-success";
		}
		if(maturities[i].date_bank != ""){
			bank_date = moment(maturities[i].date_bank).format("DD/MM/YYYY");
			item_status = "status-success";
		}

		contents += "<li class='purchase-item panel-item maturity-item "+item_status+" container-fluid' id='maturity-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"'>";
		contents += "<div class='container-fluid'>";
		contents += "<p class='col-xs-9 panel-item-title bf' id='maturity-"+maturities[i].id+"-method'><span>"+maturities[i].method+"</span> pour <span>"+maturities[i].price+"</span> €</p>";

		contents += "<p class='col-xs-1'><a href='user/"+maturities[i].transaction_user+"/achats#purchase-"+maturities[i].transaction_id+"' class='link-glyphicon'><span class='glyphicon glyphicon-share-alt glyphicon-button-alt' title='Aller à la transaction'></span></a></p>";

		// Validate reception button
		if(reception_date != ""){
			if(bank_date != ""){
				contents += "<p class='col-xs-1'><span class='glyphicon glyphicon-remove glyphicon-button glyphicon-button-alt receive-maturity disabled' data-subtype='receive-maturity' id='receive-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Annuler la réception'></span></p>";
			} else {
				contents += "<p class='col-xs-1'><span class='glyphicon glyphicon-remove glyphicon-button glyphicon-button-alt receive-maturity' data-subtype='receive-maturity' id='receive-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Annuler la réception'></span></p>";
			}
		} else {
			contents += "<p class='col-xs-1'><span class='glyphicon glyphicon-ok glyphicon-button glyphicon-button-alt trigger-sub' data-subtype='receive-maturity' id='receive-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Valider la réception'></span></p>";
		}

		if(bank_date != "")
			contents += "<p class='col-xs-1'><span class='glyphicon glyphicon-export glyphicon-button glyphicon-button-alt bank-maturity' id='bank-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Décaisser l&apos;échéance'></span></p>";
		else
			contents += "<p class='col-xs-1'><span class='glyphicon glyphicon-download-alt glyphicon-button glyphicon-button-alt bank-maturity' id='bank-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Encaisser l&apos;échéance'></span></p>";


		/*if(reception_date != "" || bank_date != "")
			contents += "<p class='col-xs-1'><span class='glyphicon glyphicon-trash glyphicon-button glyphicon-button-alt delete-maturity disabled' id='delete-maturity-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Vous ne pouvez pas supprimer une échéance déjà reçue ou encaissée'></span></p>";
		else
			contents += "<p class='col-xs-1'><span class='glyphicon glyphicon-trash glyphicon-button glyphicon-button-alt delete-maturity' id='delete-maturity-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Supprimer l&apos;échéance'></span></p>";*/

		contents += "</div>"
		contents += "<div class='container-fluid'>";
		contents += "<p class='col-xs-3'>"+maturities[i].payer+"</p>";
		if(moment(maturities[i].date) < moment() && bank_date == ""){
			deadline_class = "deadline-expired";
		}
		contents += "<p class='col-xs-2'><span class='deadline-maturity-span deadline-span "+deadline_class+"' id='deadline-maturity-span-"+maturities[i].id+"'> <span class='glyphicon glyphicon-time' title='Date de réception limite'></span> "+moment(maturities[i].date).format("DD/MM/YYYY")+"</span></p>";
		contents += "<p class='col-lg-2'><span class='glyphicon glyphicon-ok' title='Date de réception'></span> <span class='reception-span' id='reception-span-"+maturities[i].id+"'>"+reception_date+"</span></p>";
		contents += "<p class='col-lg-2'><span class='glyphicon glyphicon-download-alt' title='Date d&apos;encaissement'></span> <span class='bank-span' id='bank-span-"+maturities[i].id+"'>"+bank_date+"</span></p>";
		contents += "</div></li>";
		totalPrice += parseFloat(maturities[i].price);
	}
	return contents;
}

$(document).on('click', '.receive-maturity', function(){
	if(!$(this).hasClass("disabled")){
		var maturity_id = document.getElementById($(this).attr("id")).dataset.maturity;
		var table = "produits_echeances";
		var reception_date = $(".reception-date").val();
		if($("#receive-"+maturity_id).hasClass("glyphicon-ok")){
			// Modal to set the date and the method. If the method is credit card, the maturity will automatically be banked.
			var values = {
				date_paiement: moment(reception_date, "DD/MM/YYYY").format("DD/MM/YYYY HH:mm:ss"),
				methode_paiement: $(".reception-method").val()
			};
			if($(".reception-method").val() == "Carte bancaire"){
				values["date_encaissement"] = moment(reception_date, "DD/MM/YYYY").format("DD/MM/YYYY HH:mm:ss");
			}
		} else {
			var values = {
				date_paiement: null
			};
		}

		// As we contact updateEntry (which handles a URL), we $.param() to send the correct format
		$.when(updateEntry(table, $.param(values), maturity_id)).done(function(){
			var icon = $("#receive-"+maturity_id);
			if(icon.hasClass("glyphicon-ok")){
				icon.addClass("glyphicon-remove");
				icon.addClass("receive-maturity");
				icon.removeClass("glyphicon-ok");
				icon.removeClass("trigger-sub");
				icon.attr("title", "Annuler la réception");

				console.log(reception_date);

				$("#reception-span-"+maturity_id).text(reception_date);

				$("#maturity-"+maturity_id).addClass("status-partial-success");
				$("#maturity-"+maturity_id).removeClass("status-pre-success");
				$("#maturity-"+maturity_id).removeClass("status-over");

				// As the maturity is now received, it cannot be deleted anymore
				$("#delete-maturity-"+maturity_id).addClass("disabled");
				if($(".reception-method").val() == "Carte bancaire"){
					feedbackBankMaturity(maturity_id, moment(reception_date, "DD/MM/YYYY"));
				}
				$("#maturity-"+maturity_id+"-method>span").first().text($(".reception-method").val());
			} else {
				icon.addClass("glyphicon-ok");
				icon.addClass("trigger-sub");
				icon.removeClass("glyphicon-remove");
				icon.removeClass("receive-maturity");
				icon.attr("title", "Valider la réception");

				$("#reception-span-"+maturity_id).text("");

				// If the maturity is late, we add the status-over class
				if(moment($("#deadline-span-"+maturity_id).text(), "DD/MM/YYYY") < moment())
					$("#maturity-"+maturity_id).addClass("status-over");
				else
					$("#maturity-"+maturity_id).addClass("status-pre-success");
				$("#maturity-"+maturity_id).removeClass("status-partial-success");

				// The maturity is now pending, it can be removed
				$("#delete-maturity-"+maturity_id).removeClass("disabled");
			}
			$(".sub-modal").hide(0);
		})
	}
}).on('click', '.bank-maturity', function(){
	var maturity_id = document.getElementById($(this).attr("id")).dataset.maturity;
	var table = "produits_echeances";

	// Depending on the class of the icon, we set the date.
	if($(this).hasClass("glyphicon-download-alt")){
		var value = {
			date_encaissement: moment().format("DD/MM/YYYY HH:mm:ss")
		};
	}
	else{
		var value = {
			date_encaissement: null
		};
	}

	// As we contact updateEntry (which handles a URL), we $.param() to send the correct format
	$.when(updateEntry(table, $.param(value), maturity_id)).done(function(){
		feedbackBankMaturity(maturity_id, moment());
	});
}).on('focus', '.reception-method', function(){
	$(".reception-method").textcomplete([{
		match: /(^|\b)(\w{2,})$/,
		search: function(term, callback){
			var methods = ["Carte bancaire","Chèque n°","Espèces","Virement compte à compte","Chèques vacances","En attente"];
			callback($.map(methods, function(item){
				return item.toLowerCase().indexOf(term.toLowerCase()) === 0 ? item : null;
			}));
		},
		replace: function(item){
			return item;
		}
	}]);
}).on('click', '.bank-all', function(){
	// We bank all maturities
	$(".maturity-item:not(.status-success)").each(function(){
		var maturity_id = document.getElementById($(this).attr("id")).dataset.maturity;
		var value = {
			date_encaissement: moment().format("DD/MM/YYYY HH:mm:ss")
		};
		$.when(updateEntry("produits_echeances", $.param(value), maturity_id)).done(function(){
			feedbackBankMaturity(maturity_id, moment());
		});
	})
})
