/*
Le fichier tools.js contient toutes les fonctions javascript qui peuvent être utilisés par plusieurs fichiers,
qu'elles soient les fonctions de notification, ou des fonctions plus utilitaires.
Dès que le document est prêt, tous les modaux et les fonctions qui doivent tourner de façon constantes sont lancées ici.
*/

$(document).ready(function(){
	jQuery.expr[':'].regex = function(elem, index, match) {
		var matchParams = match[3].split(','),
			validLabels = /^(data|css):/,
			attr = {
				method: matchParams[0].match(validLabels) ?
				matchParams[0].split(':')[0] : 'attr',
				property: matchParams.shift().replace(validLabels,'')
			},
			regexFlags = 'ig',
			regex = new RegExp(matchParams.join('').replace(/^s+|s+$/g,''), regexFlags);
		return regex.test(jQuery(elem)[attr.method](attr.property));
	}

	var firstCount = 0; // Pour éviter la notification dès le rafraîchissement de la page.
	window.numberProduits = 1; // Articles dans le panier
	notifPassages(firstCount);
	notifCoursParticipants(firstCount);
	notifEcheancesDues(firstCount);
	notifPanier();
	setInterval(notifPassages, 15000);
	setInterval(notifCoursParticipants, 30000);
	setInterval(notifEcheancesDues, 30000);
	$('[data-toggle="tooltip"]').tooltip();
	moment.locale("fra");

	// Démarre l'horloge
	tickClock();
	setInterval(tickClock, 1000);

	// Construit le tableau d'inputs obligatoires par formulaire
	var mandatories = [];
	$(".mandatory").each(function(){
		$(this).prev("label").append(" <span class='span-mandatory' title='Ce champ est obligatoire'>*</span>");
		var inputName = $(this).attr('name');
		mandatories.push(inputName);
		$(this).parent().addClass('has-feedback');
		$(this).parent().append("<span class='glyphicon form-control-feedback'></span>");
		if($(this).html() != '' || $(this).val() != ''){
			$(this).parent().addClass('has-success');
			$(this).next("span").addClass('glyphicon-ok');
		}
	}).on('focus keyup change blur', function(){
		if($(this).html() != '' || $(this).val() != ''){
			$(this).parent().removeClass('has-error');
			$(this).parent().addClass('has-success');
			$(this).next("span").removeClass('glyphicon-remove');
			$(this).next("span").addClass('glyphicon-ok');
		} else {
			$(this).parent().removeClass('has-success');
			$(this).parent().addClass('has-error');
			$(this).next("span").removeClass('glyphicon-ok');
			$(this).next("span").addClass('glyphicon-remove');
		}
		var j = 0;
		for(var i = 0; i < mandatories.length; i++){
			if($("[name="+mandatories[i]+"]").val() != '' || $("[name="+mandatories[i]+"]").html() != ''){
				j++; // Incrémente le compteur d'input remplis et vide les éventuels messages d'erreurs indiquant que le champ est obligatoire
				$(this).next().children('p').empty();
			} else {
				// Affiche un message indiquant que le champ est obligatoire
				$(this).next().children('p').html("Ce champ est requis");
			}
		}
		// Si tous les inputs sont remplis, alors on autorise la soumission du formulaire
		if(j == mandatories.length){
			$("#submit-button").prop('disabled', false);
			$(".submit-button").prop('disabled', false);
		} else {
			$("#submit-button").prop('disabled', true);
			$(".submit-button").prop('disabled', false);
		}
	});

	// Editables
	$(".editable").each(function(){
		var editIcon = "<span class='glyphicon glyphicon-edit' style='display:none; float:right;'></span>";
		$(this).after(editIcon);
	});

	// Filtre dynamique
	var $rows = $('#filter-enabled tr');
	$('#search').keyup(function(){
		var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
		$rows.show().filter(function(){
			var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			return !~text.indexOf(val);
		}).hide();
	});

	// Vérification de l'existence d'un utilisateur dans la base
	$(".has-check").on('blur keyup focus',function(){
		var field = $(this);
		var identite = $(this).val();
		var token = $(this).attr('name').substr(12);
		$.post("functions/check_adherent.php", {identite : identite}).done(function(data){
			if(data == 0){
				if($(":regex(id,^unknown-user)").length == 0){
					var addOptions = "<div id='unknown-user"+token+"'>";
					addOptions += "<p>Aucun résultat. Voulez vous inscrire cet adhérent ?</p>";
					addOptions += "<a href='#user-details"+token+"' role='button' class='btn btn-info btn-block' value='create-user' id='create-user"+token+"' data-toggle='collapse' aria-expanded='false' aria-controls='userDetails'>Ouvrir le formulaire de création</a>";
					addOptions += "<div id='user-details"+token+"' class='collapse'>";
					addOptions += "<div class='well'>";
					addOptions += "<div class='row'>";
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Prénom</label><input type='text' name='identite_prenom' id='identite_prenom' class='form-control input-lg' placeholder='Prénom'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Nom</label><input type='text' name='identite_nom' id='identite_nom' class='form-control input-lg' placeholder='Nom'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "</div>"; /*row*/
					addOptions += "<div class='row'>";
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Adresse postale</label><input type='text' name='rue' id='rue' placeholder='Adresse' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>" /*col-lg-6*/
					addOptions += "<div class='col-lg-3'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Code postal</label><input type='number' name='code_postal' id='code_postal' placeholder='Code Postal' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-3*/
					addOptions += "<div class='col-lg-3'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Ville</label><input type='text' name='ville' id='ville' placeholder='Ville' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "</div>"; /*row*/
					addOptions += "<div class='row'>";
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label for='text' class='control-label'>Adresse mail</label><input type='email' name='mail' id='mail' placeholder='Adresse mail' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label for='telephone' class='control-label'>Numéro de téléphone</label><input type='tel' name='telephone' id='telephone' placeholder='Numéro de téléphone' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "</div>"; /*row*/
					addOptions += "<a class='btn btn-primary btn-block' onClick='addAdherent()'>Inscrire l'adhérent</a>";
					addOptions += "</div>"; /*well*/
					addOptions += "</div>"; /*collapse*/
					addOptions += "</div>"; /*unknown-user*/
					field.after(addOptions);
				}
			} else {
				$(":regex(id,^unknown-user)").remove();
				$(".has-name-completion:not(.completed)").val(identite);
			}
		})
	})
}).on('click', '.editable', function(){
	var methods = [
		"Carte bancaire",
		"Chèque n°",
		"Espèces",
		"Virement compte à compte",
		"Chèques vacances",
		"En attente"
	];
	// Dès le clic, on récupère la valeur initiale du champ (peu importe le type de champ)
	var initialValue = $(this).val();
	if(initialValue == ""){initialValue = $(this).html();}
	console.log(initialValue);

	// On récupère ensuite l'id du champ modifié
	var token = $(this).attr('id');

	// Si la valeur correspond à une date, alors l'action de modification sera différente
	if(initialValue.indexOf('/') != -1){
		var initialDay = initialValue.substr(0,2);
		var initialMonth = initialValue.substr(3,2);
		var initialYear = initialValue.substr(6,4);
		var initialDate = moment(new Date(initialYear+'-'+initialMonth+'-'+initialDay)).format("YYYY-MM-DD");
		$(this).replaceWith("<input type='date' class='form-control editing' id='"+token+"' value="+initialDate+">");
	} else {
		$(this).replaceWith("<input type='text' class='form-control editing' id='"+token+"' value="+initialValue+">");
	}
	$(".editing").focus();
	$(":regex(id,^methode_paiement)").autocomplete({
		source: methods
	})
	$(".editing").blur(function(){
		var editedValue = $(this).val();
		if(editedValue != "" && editedValue != initialValue){
			if(editedValue.indexOf('-') != -1){
				var editedDate = moment(new Date(editedValue)).format("DD/MM/YYYY");
				$(this).replaceWith("<span class='editable' id='"+token+"'>"+editedDate+"</span>");
			} else {
				$(this).replaceWith("<span class='editable' id='"+token+"'>"+editedValue+"</span>");
			}
			uploadChanges(token, editedValue);
		} else {
			$(this).replaceWith("<span class='editable' id='"+token+"'>"+initialValue+"</span>");
		}
	});
}).on('mouseenter', '.editable', function(){
	$(this).next().show();
}).on('mouseleave blur', '.editable', function(){
	$(this).next().hide();
}).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
	event.preventDefault();
	return $(this).ekkoLightbox({
		onNavigate: false
	});
}).on('click', '.submit-relay', function(){
	$(".submit-relay-target").click();
})

$(".has-name-completion").on('click blur keyup', function(){
	if($(this).val() != ""){
		$(this).addClass("completed");
	} else {
		$(this).removeClass("completed");
	}
})
// FONCTIONS NOTIFICATIONS //
// Fonction de surveillance des passages enregistrés. Avertit l'utilisateur et met à jour le badge de notification en cas de nouveaux enregistrements.
function notifPassages(firstCount){
	$.post("functions/watch_records.php").done(function(data){
		if(data == 0){
			$("#badge-passages").hide();
		} else {
			if(data > $("#badge-passages").html() && firstCount!=0){$.notify("Nouveaux passages enregistrés", {globalPosition: "bottom right", className:"info"});}
			$("#badge-passages").show();
			$("#badge-passages").html(data);
		}
		firstCount = 1;
	})
}

// Surveille les participations à un cours non associés à un produit (abonnement, vente spontanée, invitation...)
function notifCoursParticipants(firstCount){
	$.post("functions/watch_cours_participants.php").done(function(data){
		if(data == 0){
			$("#badge-participants").hide();
		} else {
			if(data > $("#badge-participants").html() && firstCount != 0){
				$.notify("Nouvelles participations non associées.", {globalPosition: "bottom right", className:"info"});
			}
			$("#badge-participants").show();
			$("#badge-participants").html(data);
		}
	})
}

// Surveille le nombre d'échéances qui ne sont pas réglées après leur date
function notifEcheancesDues(firstCount){
	$.post("functions/watch_maturities.php").done(function(data){
		if(data == 0){
			$("#badge-echeances").hide();
		} else {
			if(data > $("#badge-echeances").html() && firstCount != 0){
				$.notify("De nouvelles échéances ont dépassé leur date.", {globalPosition: "bottom right", className:"info"});
			}
			$("#badge-echeances").show();
			$("#badge-echeances").html(data);
		}
	})
}

// Affiche en direct le nombre d'éléments dans le panier
function notifPanier(){
	if(sessionStorage.getItem("panier") != null){
		var cartSize = JSON.parse(sessionStorage.getItem("panier"));
		if(cartSize.length == 0){
			$("#badge-panier").hide();
			$(".table-panier").empty();
		} else {
			$("#badge-panier").show();
			$("#badge-panier").html(cartSize.length);
			fillShoppingCart();
		}
	}
}

function showSuccessNotif(data){
	$.notify(data, {globalPosition:"right bottom", className:"success"});
}

// FONCTIONS UTILITAIRES //
// Insert la date d'aujourd'hui dans un input de type date supportant la fonctionnalité
$("*[date-today='true']").click(function(){
	var today = new moment().format("YYYY-MM-DD");
	$(this).parent().prev().val(today);
	$(this).parent().prev().blur();
});

// Convertit une date en temps relatif. (ex: "il y a un jour")
$(".relative-start").each(function(){
	$(this).html(moment($(this).html(), "YYYY-MM-DD HH:ii:ss", 'fr').fromNow());
});

// Vérifie si un adhérent a des échéances impayées lors de la vente d'un forfait
function checkMaturities(data){
	$.post('functions/check_unpaid.php', {search_id : data}).done(function(maturities){
		if(maturities != 0){
			$('#err_adherent').empty();
			$('#unpaid').show();
			$("#maturities-checked").hide();
		} else {
			$('#err_adherent').empty();
			$('#unpaid').hide();
			$("#maturities-checked").show();
		}
	})
}

// Effectue une inscription rapide dans le cas d'un adhérent inexistant à la réservation d'une salle ou l'achat d'un forfait
function addAdherent(){
	var identite_prenom = $('#identite_prenom').val();
	var identite_nom = $('#identite_nom').val();
	var rfid = $("[name='rfid']").val();
	var rue = $('#rue').val();
	var code_postal = $('#code_postal').val();
	var ville = $('#ville').val();
	var mail = $('#mail').val();
	var telephone = $('#telephone').val();
	var date_naissance = $('#date_naissance').val();
	$.post("functions/add_adherent.php", {identite_prenom : identite_prenom, identite_nom : identite_nom, rfid : rfid, rue : rue, code_postal : code_postal, ville : ville, mail : mail, telephone : telephone, date_naissance : date_naissance}).done(function(data){
		var parse = JSON.parse(data);
		$(".has-name-completion:not(.completed)").val(identite_prenom+" "+identite_nom);
		if(window.miniCart != ""){
			window.miniCart["id_beneficiaire"] = parse["id"];
			window.miniCart["nom_beneficiaire"] = identite_prenom+" "+identite_nom;
		}
		showSuccessNotif(parse["success"]);
		$(":regex(id,^unknown-user)").hide('500');
	});
}

// Vérifie l'existence de jours chômés à l'ajout d'un évènement
function checkHoliday(){
	var date_debut = $('#date_debut').val();
	$.post("functions/check_holiday.php", {date_debut : date_debut}).done(function(data){
		console.log(data);
		if(data != "0"){
			$("#holiday-alert").empty();
			$("#holiday-alert").append("Ce jour est chômé. Impossible d'ajouter une réservation à cette date.");
			$('.confirm-add').prop('disabled', true);
		} else {
			$('#holiday-alert').empty();
			$('.confirm-add').prop('disabled', false);
			checkCalendar(true, false);
		}
	});
}

// Afficher et met à jour l'horloge
function tickClock(){
	var now = moment().locale('fr').format("DD MMMM YYYY HH:mm:ss");
	$("#current-time").html(now);
	$(".panel").each(function(){
		$(this).find(".cours-count").html($(this).find(".list-group-item").length);
		$(this).find(".cours-count-checked").html($(this).find(".list-group-item-success").length);
	})
}

// Remplit le popover de l'icône panier dans la navigation
function fillShoppingCart(){
	$(".table-panier").empty();
	if(sessionStorage.getItem("panier") != null){
		var cartSize = JSON.parse(sessionStorage.getItem("panier"));
		var line = "";
		for(var i = 0; i < cartSize.length; i++){
			line += "<tr>"
			line += "<td class='col-lg-11'>"+cartSize[i]+"</td>";
			line += "<td class='col-lg-1'><span class='glyphicon glyphicon-trash' onclick='removeCartElement("+i+")'></span></td>";
			line += "<tr>";
		}
		$(".table-panier").append(line);
	}
}

function removeCartElement(key){
	var cart = JSON.parse(sessionStorage.getItem("panier"));
	cart.splice(key, 1);
	sessionStorage.setItem("panier", JSON.stringify(cart));
	notifPanier();
}

// Compose les URL lors de l'achat
function composeURL(token){
	var url = "personnalisation.php?element=";
	url += token;
	url += "&order=0";
	$("[name='next']").attr('href', url);
	$("[name='previous']").attr('href', url);
}

$(".draggable").draggable({
	snap: ".list-group",
	axis: "y"
});
$(".droppable").droppable({
	drag: function(event, ui){
		//$(this).height($(this).height + ui.draggable.height());
	},
	drop: function(event, ui){
		ui.draggable.detach().appendTo($(this));
	}
});
