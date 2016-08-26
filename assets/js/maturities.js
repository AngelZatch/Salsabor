function updateMaturityState(maturity_id){
	/*
	- over : not received, not banked, deadline is before today
	- pre-success : not received, not banked, deadline is after today
	- partial-success : received, not banked, deadline doesn't matter
	- success : received, banked, deadline doesn't matter
	*/
	var maturity_line = $("#maturity-"+maturity_id);
	var deadline_span = $("#deadline-"+maturity_id);
	var reception_span = $("#reception-span-"+maturity_id);
	var bank_span = $("#bank-span-"+maturity_id);

	maturity_line.removeClass("status-pre-success");
	maturity_line.removeClass("status-success");
	maturity_line.removeClass("status-partial-success");
	maturity_line.removeClass("status-over");

	if(reception_span.text() != ""){
		maturity_line.addClass("status-partial-success");
	}
	if(bank_span.text() != ""){
		// Clean previous if, for credit cards.
		maturity_line.removeClass("status-partial-success");
		maturity_line.addClass("status-success");
	}
	if(bank_span.text() == "" && reception_span.text() == ""){
		if(moment(deadline_span.text(), "DD/MM/YYYY") < moment()){
			deadline_span.addClass("deadline-expired");
			maturity_line.addClass("status-over");
		} else {
			deadline_span.removeClass("deadline-expired");
			maturity_line.addClass("status-pre-success");
		}
	} else {
		deadline_span.removeClass("deadline-expired");
	}
}

function displayMaturities(maturities){
	var totalPrice = 0, contents = "";
	for(var i = 0; i < maturities.length; i++){
		var item_status = "status-pre-success", reception_date = "", bank_date = "", deadline_class = "", method = maturities[i].method;
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
		contents += "<p class='col-xs-10 panel-item-title bf' id='maturity-"+maturities[i].id+"-method'><span>"+maturities[i].method+"</span> pour <span>"+maturities[i].price+"</span> €</p>";

		var redirection_link = "user/"+maturities[i].transaction_user+"/achats";

		if(top.location.pathname != "/Salsabor/"+redirection_link){
			contents += "<p class='col-xs-1'><a href='user/"+maturities[i].transaction_user+"/achats#purchase-"+maturities[i].transaction_id+"' class='link-glyphicon'><span class='glyphicon glyphicon-share-alt glyphicon-button-alt' title='Aller à la transaction'></span></a></p>";
		} else {
			contents += "<p class='col-xs-1'></p>";
		}

		contents += "<p class='col-xs-1'><span class='glyphicon glyphicon-trash glyphicon-button glyphicon-button-alt bank-maturity' id ='delete-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Supprimer l&apos;échéance'></span></p>";

		contents += "</div>"
		contents += "<div class='container-fluid'>";
		contents += "<p class='col-xs-3'>"+maturities[i].payer+"</p>";

		// Deadline
		if(moment(maturities[i].date) < moment() && bank_date == ""){
			deadline_class = "deadline-expired";
		}
		contents += "<p class='col-xs-2 trigger-sub trigger-editable "+deadline_class+"' data-subtype='deadline-maturity' id='deadline-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Modifier la date limite'><span class='glyphicon glyphicon-time' title='Date de réception limite'></span> <span class='deadline-maturity-span' id='deadline-maturity-span-"+maturities[i].id+"'>"+moment(maturities[i].date).format("DD/MM/YYYY")+"</span></p>";

		// Reception
		contents += "<p class='col-lg-2 trigger-sub trigger-editable' data-subtype='receive-maturity' id='receive-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Valider la réception'><span class='glyphicon glyphicon-ok' title='Date de réception'></span> <span class='reception-span' id='reception-span-"+maturities[i].id+"'>"+reception_date+"</span></p>";

		// Bank
		contents += "<p class='col-lg-2 trigger-sub trigger-editable' data-subtype='bank-maturity' id='bank-"+maturities[i].id+"' data-maturity='"+maturities[i].id+"' title='Encaisser l&apos;échéance'><span class='glyphicon glyphicon-download-alt' title='Date d&apos;encaissement'></span> <span class='bank-span' id='bank-span-"+maturities[i].id+"'>"+bank_date+"</span></p>";
		contents += "</div></li>";
		totalPrice += parseFloat(maturities[i].price);
	}
	return contents;
}

$(document).on('click', '.receive-maturity', function(){
	var maturity_id = document.getElementById($(this).attr("id")).dataset.maturity;
	var table = "produits_echeances";
	var reception_date = $(".reception-date").val();
	// Modal to set the date and the method. If the method is credit card, the maturity will automatically be banked.
	var values = {
		date_paiement: moment(reception_date, "DD/MM/YYYY").format("DD/MM/YYYY HH:mm:ss"),
		methode_paiement: $(".reception-method").val()
	};
	if($(".reception-method").val() == "Carte bancaire"){
		values["date_encaissement"] = moment(reception_date, "DD/MM/YYYY").format("DD/MM/YYYY HH:mm:ss");
	}

	// As we contact updateEntry (which handles a URL), we $.param() to send the correct format
	$.when(updateEntry(table, $.param(values), maturity_id)).done(function(){
		$(".sub-modal").hide(0);
		$("#maturity-"+maturity_id+"-method>span").first().text($(".reception-method").val());
		$("#reception-span-"+maturity_id).text(reception_date);
		if($(".reception-method").val() == "Carte bancaire"){
			$("#bank-span-"+maturity_id).text(reception_date);
		}
		updateMaturityState(maturity_id);
	})
}).on('click', '.cancel-reception', function(){
	var maturity_id = document.getElementById($(this).attr("id")).dataset.maturity;
	var table = "produits_echeances";

	// We cancel the date of reception.
	var value = {
		date_paiement: null
	};

	$.when(updateEntry(table, $.param(value), maturity_id)).done(function(){
		$(".sub-modal").hide(0);
		$("#reception-span-"+maturity_id).text("");
		updateMaturityState(maturity_id);
	})
}).on('click', '.bank-maturity', function(){
	var maturity_id = document.getElementById($(this).attr("id")).dataset.maturity;
	var table = "produits_echeances";
	var bank_date = $(".bank-date").val();

	// Depending on the class of the icon, we set the date.
	var value = {
		date_encaissement: moment(bank_date, "DD/MM/YYYY").format("DD/MM/YYYY HH:mm:ss")
	};

	// As we contact updateEntry (which handles a URL), we $.param() to send the correct format
	$.when(updateEntry(table, $.param(value), maturity_id)).done(function(){
		$(".sub-modal").hide(0);
		$("#bank-span-"+maturity_id).text(moment(bank_date, "DD/MM/YYYY").format("DD/MM/YYYY"));
		updateMaturityState(maturity_id);
	});
}).on('click', '.cancel-bank', function(){
	var maturity_id = document.getElementById($(this).attr("id")).dataset.maturity;
	var table = "produits_echeances";
	var value = {
		date_encaissement: null
	};
	$.when(updateEntry(table, $.param(value), maturity_id)).done(function(){
		$(".sub-modal").hide(0);
		$("#bank-span-"+maturity_id).text("");
		updateMaturityState(maturity_id);
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
			updateMaturityState(maturity_id);
		});
	})
}).on('click', '.deadline-maturity', function(){
	var maturity_id = document.getElementById($(this).attr("id")).dataset.maturity;
	var table = "produits_echeances";
	var deadline = $(".deadline-date").val();
	var value = {
		date_echeance: moment(deadline, "DD/MM/YYYY").format("DD/MM/YYYY HH:mm:ss")
	};

	$.when(updateEntry(table, $.param(value), maturity_id)).done(function(){
		$(".sub-modal").hide(0);
		$("#deadline-maturity-span-"+maturity_id).text(deadline);
		updateMaturityState(maturity_id);
	})
})
